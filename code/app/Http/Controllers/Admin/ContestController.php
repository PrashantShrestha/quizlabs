<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\User;
use App\Models\Question;
use App\Models\QuizInfo;
use Illuminate\Http\Request;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;

class ContestController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Contest';
        $type = Type::where('act', 'contest')->first();
        $contests = QuizInfo::where('type_id', $type->id)->searchable(['title'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.contest.index', compact('pageTitle', 'contests'));
    }

    public function create()
    {
        $pageTitle = 'New Contest Create';
        $type = Type::where('act', 'contest')->first();
        return view('admin.contest.form', compact('pageTitle', 'type'));
    }

    public function store(Request $request)
    {
        $date = explode('-', $request->date);

        if (count($date) != 2) {
            $notify[] = ['error', 'Start date and end date required'];
            return back()->withNotify($notify);
        }

        $date = [
            'start_date' => trim($date[0]),
            'end_date' => trim($date[1])
        ];

        $request->merge($date);

        $request->validate([
            'title'         => 'required',
            'description'   => 'required',
            'point'         => 'required|integer',
            'prize'         => 'required|integer',
            'contest_image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after:start_date',
            'winning_mark'  => 'required|integer|between:1,100'
        ]);

        if ($request->contest_id) {
            $quizInfo = QuizInfo::findOrFail($request->contest_id);
            $old = $quizInfo->image;
            $message = 'Contest updated successfully';
        } else {
            $quizInfo = new QuizInfo();
            $old = null;
            $message = 'Contest added successfully';
        }

        if ($request->hasFile('contest_image')) {
            try {
                $quizInfo->image = fileUploader($request->contest_image, getFilePath('contest'), getFileSize('contest'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $quizInfo->type_id      = $request->type_id;
        $quizInfo->title        = $request->title;
        $quizInfo->description  = $request->description;
        $quizInfo->point        = $request->point;
        $quizInfo->start_date   = Carbon::parse($date['start_date'])->format('Y-m-d');
        $quizInfo->end_date     = Carbon::parse($date['end_date'])->format('Y-m-d');
        $quizInfo->prize        = $request->prize;
        $quizInfo->winning_mark = $request->winning_mark / 100;
        $quizInfo->save();

        $notify[] = ['success', $message];
        return to_route('admin.contest.details', $quizInfo->id)->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Contest';
        $contest = QuizInfo::findOrFail($id);
        $type = Type::where('act', 'contest')->first();
        return view('admin.contest.form', compact('pageTitle', 'contest', 'type'));
    }

    public function contestDetails($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = $quizInfo->title;

        $questions = Question::whereHas('quizInfos', function ($q) use ($id) {
            $q->where('id', $id);
        })->searchable(['question'])->orderBy('id', 'desc')->paginate(getPaginate());

        $route = [
            'create' => 'admin.contest.question.create',
            'edit' => 'admin.contest.question.edit',
            'import' => 'admin.contest.question.import'
        ];

        return view('admin.question.list', compact('pageTitle', 'quizInfo', 'questions', 'route'));
    }

    public function addQuestion($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = 'Add Question';
        $backUrl = route('admin.contest.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'backUrl'));
    }

    public function editQuestion($id, $quizInfo_id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::where('id', $id)->with('quizInfos', function ($q) use ($quizInfo_id) {
            $q->where('id', $quizInfo_id);
        })->firstOrFail();
        $quizInfo = $question->quizInfos[0];
        $backUrl = route('admin.contest.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'question', 'backUrl'));
    }

    public function questionImport($id)
    {
        $pageTitle = 'Import Questions';
        $backUrl = route('admin.contest.details', $id);
        return view('admin.question.import', compact('pageTitle', 'backUrl', 'id'));
    }

    public function sendNotification($id)
    {
        $contest   = QuizInfo::where('status', 1)->findOrFail($id);
        $users     = User::active()->cursor();
        $shortCode = [
            'title' => $contest->title,
            'date' => $contest->start_date
        ];

        foreach ($users as $user) {
            notify($user, 'SEND_NEW_CONTEST', $shortCode);
        }
        $notify[] = ['success', 'Notification send successfully'];
        return back()->withNotify($notify);
    }

    public function changeStatus($id)
    {
        return QuizInfo::changeStatus($id);
    }
}
