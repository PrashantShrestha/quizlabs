<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Question;
use App\Models\QuizInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DailyQuizController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Daily Quiz';
        $type = Type::where('act', 'daily_quiz')->first();
        $dailyQuizzes = QuizInfo::where('type_id', $type->id)->searchable(['title'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.daily_quiz.index', compact('pageTitle', 'type', 'dailyQuizzes'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'title'             => 'required|date|after_or_equal:today'. $id,
            'point'             => 'required|integer',
            'prize'             => 'required|integer',
            'type_id'           => 'required|integer',
            'winning_mark'      => 'required|integer|between:1,100'
        ]);

        if ($id) {
            $quizInfo = QuizInfo::findOrFail($id);
            $checkQuizInfo = QuizInfo::where('title', Carbon::parse($request->title)->format('Y-m-d'))->first();
            if($checkQuizInfo){
                $notify[] = ['error', 'This daily quiz already exist.'];
                return back()->withNotify($notify);
            }
            $message = 'Daily quiz updated successfully';
        } else {
            $quizInfo = new QuizInfo();
            $message = 'Daily quiz added successfully';
        }
        $title = Carbon::parse($request->title)->format('Y-m-d');

        $quizInfo->type_id          = $request->type_id;
        $quizInfo->title            = $title;
        $quizInfo->winning_mark     = $request->winning_mark / 100;
        $quizInfo->point            = $request->point;
        $quizInfo->prize            = $request->prize;
        $quizInfo->save();

        $notify[] = ['success', $message];
        return to_route('admin.daily.quiz.details', $quizInfo->id)->withNotify($notify);
    }

    public function dailyQuizDetails($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = $quizInfo->title;

        $questions = Question::whereHas('quizInfos', function($q) use ($id) {
            $q->where('id', $id);
        })->searchable(['question'])->orderBy('id', 'desc')->paginate(getPaginate());

        $route = [
            'create' => 'admin.daily.quiz.question.create',
            'edit' => 'admin.daily.quiz.question.edit',
            'import' => 'admin.daily.quiz.question.import'
        ];

        return view('admin.question.list', compact('pageTitle', 'quizInfo', 'questions', 'route'));
    }

    public function addQuestion($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = 'Add Question';
        $backUrl = route('admin.daily.quiz.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'backUrl'));
    }

    public function editQuestion($id, $quizInfo_id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::where('id', $id)->with('quizInfos', function($q)use($quizInfo_id){
            $q->where('id', $quizInfo_id);
        })->firstOrFail();
        $quizInfo = $question->quizInfos[0];
        $backUrl = route('admin.daily.quiz.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'question', 'backUrl'));
    }

    public function questionImport($id)
    {
        $pageTitle = 'Import Questions';
        $backUrl = route('admin.daily.quiz.details', $id);
        return view('admin.question.import', compact('pageTitle', 'backUrl', 'id'));
    }

    public function sendNotification($id)
    {
        $dailyQuiz   = QuizInfo::where('status', 1)->findOrFail($id);
        $users     = User::active()->cursor();
        $shortCode = [
            'prize' => $dailyQuiz->prize
        ];

        foreach ($users as $user) {
            notify($user, 'SEND_NEW_DAILY_QUIZ', $shortCode);
        }
        $notify[] = ['success', 'Notification send successfully'];
        return back()->withNotify($notify);
    }

    public function changeStatus($id)
    {
        return QuizInfo::changeStatus($id);
    }
}
