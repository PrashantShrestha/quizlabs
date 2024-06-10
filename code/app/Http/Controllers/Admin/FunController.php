<?php

namespace App\Http\Controllers\Admin;

use App\Models\Type;
use App\Models\User;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuizInfo;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FunController extends Controller
{
    public function index()
    {
        $pageTitle = 'All Fun \'N\' Learn';
        $type = Type::where('act', 'fun')->firstOrFail();
        $categories = Category::active()->with(['subcategories' => function($subcategories){
            $subcategories->active();
        }])->get();
        $funs = QuizInfo::where('type_id', $type->id)->with('category')->searchable(['title', 'category:name'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.fun.index', compact('pageTitle', 'funs', 'type', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|unique:quiz_infos,title,' . $request->fun_id,
            'point'             => 'required|numeric',
            'prize'             => 'required|numeric',
            'detail'            => 'required',
            'type_id'           => 'required',
            'category_id'       => 'required',
            'winning_mark'      => 'required|integer|between:1,100'
        ]);


        if ($request->fun_id) {
            $quizInfo = QuizInfo::findOrFail($request->fun_id);
            $message = 'Fun \'N\' Learn updated successfully';
        } else {
            $quizInfo = new QuizInfo();
            $message = 'Fun \'N\' Learn added successfully';
        }

        $quizInfo->type_id         = $request->type_id;
        $quizInfo->title           = $request->title;
        $quizInfo->point           = $request->point;
        $quizInfo->prize           = $request->prize;
        $quizInfo->description     = $request->detail;
        $quizInfo->category_id     = $request->category_id;
        $quizInfo->sub_category_id = $request->subcategory_id ?? 0;
        $quizInfo->winning_mark    = $request->winning_mark / 100;
        $quizInfo->save();

        $notify[] = ['success', $message];
        return to_route('admin.fun.details', $quizInfo->id)->withNotify($notify);
    }

    public function funDetails($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = $quizInfo->title;

        $questions = Question::whereHas('quizInfos', function($q) use ($id) {
            $q->where('id', $id);
        })->searchable(['question'])->orderBy('id', 'desc')->paginate(getPaginate());

        $route = [
            'create' => 'admin.fun.question.create',
            'edit' => 'admin.fun.question.edit',
        ];

        return view('admin.question.list', compact('pageTitle', 'quizInfo', 'questions', 'route'));
    }

    public function addQuestion($id)
    {
        $quizInfo = QuizInfo::findOrFail($id);
        $pageTitle = 'Add Question';
        $backUrl = route('admin.fun.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'backUrl'));
    }

    public function editQuestion($id, $quizInfo_id)
    {
        $pageTitle = 'Edit Question';
        $question = Question::where('id', $id)->with('quizInfos', function($q)use($quizInfo_id){
            $q->where('id', $quizInfo_id);
        })->firstOrFail();
        $quizInfo = $question->quizInfos[0];
        $backUrl = route('admin.fun.details', $quizInfo->id);
        return view('admin.question.form', compact('pageTitle', 'quizInfo', 'question', 'backUrl'));
    }

    public function sendNotification($id)
    {
        $contest   = QuizInfo::where('status', 1)->findOrFail($id);
        $users     = User::active()->cursor();
        $shortCode = [
            'title' => $contest->title,
        ];

        foreach ($users as $user) {
            notify($user, 'SEND_NEW_FUN', $shortCode);
        }
        $notify[] = ['success', 'Notification send successfully'];
        return back()->withNotify($notify);
    }

    public function changeStatus($id)
    {
        return QuizInfo::changeStatus($id);
    }
}
