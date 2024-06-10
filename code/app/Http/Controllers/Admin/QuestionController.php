<?php

namespace App\Http\Controllers\Admin;

use App\Models\Type;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizInfo;
use App\Constants\Status;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Exception;

class QuestionController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Questions';
        $types = Type::where('act', '!=', 'guess_word')->get();
        $categories = Category::active()->with(['subcategories' => function($subcategories){
            $subcategories->active();
        }])->get();
        $questions = Question::whereDoesntHave('quizInfos', function($quizInfos){
            $quizInfos->whereHas('type' , function($type){
                $type->where('act', 'guess_word');
            });
        })->searchable(['question'])
            ->filter(['quizInfos:type_id', 'quizInfos:category_id', 'quizInfos:sub_category_id'])
            ->orderBy('id', 'desc')->paginate(getPaginate());

        return view('admin.question.all', compact('pageTitle', 'questions', 'types', 'categories'));
    }

    public function add()
    {
        $pageTitle = 'Add Question';
        $backUrl = route('admin.question.index');
        return view('admin.question.form', compact('pageTitle', 'backUrl'));
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::with('quizInfos', 'options')->findOrFail($id);
        $backUrl = route('admin.question.index');
        return view('admin.question.form', compact('pageTitle', 'question', 'backUrl'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_image'    => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'question'          => 'required',
            'option.*'          => 'required',
            'level'             => 'nullable|integer',
            'is_answer'         => 'required'
        ]);

        $question = $this->createQuestion($request);
        $this->createOptions($request, $question);
        $this->createQuizInfoRelation($request, $question);

        if (request()->question_id) {
            $notify[] = ['success', 'Question updated successfully'];
        } else {
            $notify[] = ['success', 'Question added successfully'];
        }
        return back()->withNotify($notify);
    }

    protected function createQuestion($request)
    {
        if (@$request->question_id) {
            $question = Question::findOrFail($request->question_id);
            $old = $question->image;
        } else {
            $question = new Question();
        }

        if ($request->hasFile('question_image')) {
            try {
                $question->image = fileUploader($request->question_image, getFilePath('question'), null, @$old);
            } catch (\Exception $exp) {
                throw ValidationException::withMessages(['error' => 'Couldn\'t upload your image']);
            }
        }
        $question->question     = $request->question;
        $question->save();

        if (!@$request->question_id) {
            $question->code = Str::random(2) . $question->id;
            $question->save();
        }

        return $question;
    }

    protected function createOptions($request, $question)
    {
        if ($request->question_id) {
            $question->options()->delete();
        }

        foreach ($request->option as $key => $value) {
            $option = new QuestionOption();
            $option->question_id = $question->id;
            $option->option = $value;
            if (in_array($key + 1, $request->is_answer)) {
                $option->is_answer = Status::YES;
            }
            $option->save();
        }
    }

    protected function createQuizInfoRelation($request, $question)
    {
        if ($request->quizInfo_id) {
            if (!$request->question_id) {
                $contest = QuizInfo::findOrFail($request->quizInfo_id);
                $contest->questions()->attach($question->id);
            }
        }
    }

    public function questionImport($id)
    {
        request()->search = substr(request()->search, 2);
        $questions = Question::whereDoesntHave('quizInfos', function ($q) use ($id) {
            $q->where('id', $id)
                ->orWhereHas("type", function ($query) {
                    $query->where('act', 'fun');
                })
                ->orWhereHas("type", function ($query) {
                    $query->where('act', 'guess_word');
                })
                ->orWhere('end_date', '>', today());
        })
            ->searchable(['question', 'id'])
            ->orderBy('id', 'desc')->paginate(getPaginate(5));

        return response()->json([
            'success'       => true,
            'questions'     => $questions,
            'more'          => $questions->hasMorePages()
        ]);
    }

    public function questionImportUpdate(Request $request)
    {
        $request->validate([
            'question.*'    => 'required',
            'quizInfo_id'   => 'required'
        ]);

        if ($request->quizInfo_id) {
            foreach ($request->question as $id) {
                $quizInfo = QuizInfo::findOrFail($request->quizInfo_id);
                $quizInfo->questions()->attach($id);
            }
        }
        $notify[] = ['success', 'Question added successfully'];
        return back()->withNotify($notify);
    }

    public function optionDelete($id)
    {
        $option = QuestionOption::findOrFail($id);
        $option->delete();

        $notify[] = ['success', 'Option deleted successfully'];
        return back()->withNotify($notify);
    }

        public function importCsvQuestion(Request $request)
    {
        try {
            $import = importFileReader($request->file, ['question', 'answer', 'option-1', 'option-2', 'option-3', 'option-4']);
            $notify[] = ['success',@$import->notify['message']];
            return back()->withNotify($notify);
        } catch (Exception $ex) {
            $notify[] = ['error', $ex->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function changeStatus($id)
    {
        return Question::changeStatus($id);
    }
}
