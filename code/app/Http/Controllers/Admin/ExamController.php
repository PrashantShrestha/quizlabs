<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Question;
use App\Models\QuizInfo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Exams';
        $type = Type::where('act', 'exam')->first();
        $exams = QuizInfo::where('type_id', $type->id)->searchable(['title'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.live_exam.index', compact('pageTitle', 'exams', 'type'));
    }

    public function create()
    {
        $pageTitle = 'New Exam Create';
        $type = Type::where('act', 'exam')->first();
        return view('admin.live_exam.form', compact('pageTitle', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exam_image'        => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'title'             => 'required',
            'description'       => 'required',
            'point'             => 'required|integer',
            'prize'             => 'required|integer',
            'start_date'        => 'required|date|after_or_equal:today',
            'exam_start_time'   => 'required|date_format:h:i a',
            'exam_duration'     => 'required|integer',
            'type_id'           => 'required|integer',
            'winning_mark'      => 'required|integer|between:1,100'
        ]);

        if ($request->exam_id) {
            $quizInfo = QuizInfo::findOrFail($request->exam_id);
            $old = $quizInfo->image;
            $message = 'Exam updated successfully';
        } else {
            $quizInfo = new QuizInfo();
            $old = null;
            $quizInfo->exam_key = ucwords(Str::random(6));
            $message = 'Exam added successfully';
        }

        if ($request->hasFile('exam_image')) {
            try {
                $quizInfo->image = fileUploader($request->exam_image, getFilePath('exam'), getFileSize('exam'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $quizInfo->type_id          = $request->type_id;
        $quizInfo->title            = $request->title;
        $quizInfo->description      = $request->description;
        $quizInfo->point            = $request->point;
        $quizInfo->prize            = $request->prize;
        $quizInfo->start_date       = Carbon::parse($request->start_date)->format('Y-m-d');
        $quizInfo->end_date         = Carbon::parse($request->start_date)->format('Y-m-d');
        $quizInfo->exam_start_time  = Carbon::parse($request->exam_start_time)->format('H:i');
        $quizInfo->exam_end_time    = Carbon::parse($request->exam_start_time)->addMinutes($request->exam_duration)->format('H:i');
        $quizInfo->exam_duration    = $request->exam_duration;
        $quizInfo->winning_mark     = $request->winning_mark / 100;
        $quizInfo->exam_rule        = $request->exam_rule;
        $quizInfo->save();

        $notify[] = ['success', $message];
        return to_route('admin.exam.details', $quizInfo->id)->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Exam';
        $exam = QuizInfo::findOrFail($id);
        $type = Type::where('act', 'exam')->first();
        return view('admin.live_exam.form', compact('pageTitle', 'exam', 'type'));
    }

    public function examDetails($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = $quizInfo->title;

        $questions = Question::whereHas('quizInfos', function($q) use ($id) {
            $q->where('id', $id);
        })->searchable(['question'])->orderBy('id', 'desc')->paginate(getPaginate());

        $route = [
            'create' => 'admin.exam.question.create',
            'edit' => 'admin.exam.question.edit',
            'import' => 'admin.exam.question.import'
        ];

        return view('admin.question.list', compact('pageTitle', 'quizInfo', 'questions', 'route'));
    }

    public function addQuestion($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = 'Add Question';
        $backUrl = route('admin.exam.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'backUrl'));
    }

    public function editQuestion($id, $quizInfo_id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::where('id', $id)->with('quizInfos', function($q)use($quizInfo_id){
            $q->where('id', $quizInfo_id);
        })->firstOrFail();
        $quizInfo = $question->quizInfos[0];
        $backUrl = route('admin.exam.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'question', 'backUrl'));
    }

    public function sendNotification($id)
    {
        $exam   = QuizInfo::where('status', 1)->findOrFail($id);
        $users     = User::active()->cursor();
        $shortCode = [
            'title' => $exam->title,
            'date' => $exam->start_date,
            'time' => $exam->exam_start_time
        ];

        foreach ($users as $user) {
            notify($user, 'SEND_LIVE_EXAM', $shortCode);
        }
        $notify[] = ['success', 'Notification send successfully'];
        return back()->withNotify($notify);
    }
    
    public function questionImport($id)
    {
        $pageTitle = 'Import Questions';
        $backUrl = route('admin.exam.details', $id);
        return view('admin.question.import', compact('pageTitle', 'backUrl', 'id'));
    }

    public function changeStatus($id)
    {
        return QuizInfo::changeStatus($id);
    }
}
