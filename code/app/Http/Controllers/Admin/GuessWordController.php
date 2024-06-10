<?php

namespace App\Http\Controllers\Admin;

use App\Models\Type;
use App\Models\Level;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizInfo;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuessWordController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Guess Word';
        $type = Type::where('act', 'guess_word')->first();
        $quizInfos = QuizInfo::where('type_id', $type->id)
            ->with('category', 'subcategory', 'level')
            ->searchable(['category:name', 'subcategory:name'])
            ->orderBy('id', 'desc')->paginate(getPaginate());

        $levels = Level::all();
        $categories = Category::active()->with(['subcategories' => function($subcategories){
            $subcategories->active();
        }])->get();
        return view('admin.guess_word.index', compact('pageTitle', 'categories', 'quizInfos', 'levels', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id'           => 'required',
            'category_id'       => 'required',
            'level'             => 'required',
            'winning_mark'      => 'required|integer|between:1,100'
        ]);



        $requestQuizInfo = QuizInfo::where('type_id', $request->type_id)->where('category_id', $request->category_id)
            ->where('sub_category_id', @$request->subcategory_id)
            ->where('level_id', $request->level)->first();

        if ($request->general_quiz_id) {
            $quizInfo = QuizInfo::findOrFail($request->general_quiz_id);

            if(@$requestQuizInfo && (@$requestQuizInfo->id != $quizInfo->id)){
                $notify[] = ['success', 'You have already add this guess word.'];
                return back()->withNotify($notify);
            }
            $message = 'General quiz updated successfully';
        } else {
            if ($requestQuizInfo) {
                $notify[] = ['success', 'You have already add this guess word.'];
                return back()->withNotify($notify);
            }
            $quizInfo = new QuizInfo();
            $message = 'General quiz added successfully';
        }

        $quizInfo->type_id          = $request->type_id;
        $quizInfo->category_id      = $request->category_id;
        $quizInfo->sub_category_id  = @$request->subcategory_id ?? 0;
        $quizInfo->level_id         = $request->level;
        $quizInfo->winning_mark     = $request->winning_mark / 100;
        $quizInfo->save();

        $notify[] = ['success', $message];
        return to_route('admin.guess.list', $quizInfo->id)->withNotify($notify);
    }

    public function questionList($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = "Guess Word Questions";

        $questions = Question::whereHas('quizInfos', function ($q) use ($id) {
            $q->where('id', $id);
        })->searchable(['question'])->orderBy('id', 'desc')->paginate(getPaginate());

        $route = [
            'create' => 'admin.guess.question.create',
            'edit' => 'admin.guess.question.edit'
        ];

        return view('admin.question.list', compact('pageTitle', 'quizInfo', 'questions', 'route'));
    }

    public function addQuestion($id)
    {
        $pageTitle = 'Add Question';
        $type = Type::where('act', 'guess_word')->first();
        $quizInfo = QuizInfo::findOrFail($id);
        $backUrl = route('admin.guess.list', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'backUrl', 'quizInfo', 'type'));
    }

    public function editQuestion($id, $quizInfo_id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::where('id', $id)->with('quizInfos', function ($q) use ($quizInfo_id) {
            $q->where('id', $quizInfo_id);
        })->firstOrFail();
        $quizInfo = $question->quizInfos[0];

        $type = Type::where('act', 'guess_word')->first();

        $backUrl = route('admin.guess.list', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'question', 'backUrl', 'type'));
    }

    public function changeStatus($id)
    {
        if (!QuizInfo::findOrFail($id)->level_id) {
            $notify[] = ['error', 'Set level first'];
            return back()->withNotify($notify);
        }
        return QuizInfo::changeStatus($id);
    }
}
