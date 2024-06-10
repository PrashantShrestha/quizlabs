<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\PlayInfo;
use App\Models\Question;
use App\Models\QuizInfo;
use App\Constants\Status;
use App\Models\SubCategory;
use App\Models\PlayQuestion;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GeneralQuizController extends Controller
{
    public function categories()
    {
        $categories = Category::active()
            ->questionCount('general')
            ->subcategoryCount('general')
            ->with(['quizInfos' => function($quizInfo){
                $quizInfo->quizInfoLists('general');
            }, 'quizInfos.subcategory' => function($q){
                $q->active();
            },'quizInfos.playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }, 'quizInfos.level'])
            ->get();

        if (count($categories) == 0) {
            $notify[] = 'Category list empty';
        }else{
            foreach($categories as $key => $category){
                $categories[$key] = levelStatus($category);
            }
            $notify[] = 'Category List';
        }

        return response()->json([
            'remark'    => 'general_quiz_category',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'categories' => $categories,
                'image_path' => asset(getFilePath('category')),
            ]
        ]);
    }

    public function subcategory($categoryId)
    {
        $subcategories = SubCategory::active()
            ->where('category_id', $categoryId)
            ->whereHas('quizInfos')
            ->questionCount('general')
            ->with(['quizInfos' => function ($quizInfo) {
                $quizInfo->quizInfoLists('general');
            }, 'quizInfos.level', 'quizInfos.playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
            ->get();

        if(count($subcategories) > 0){
            foreach($subcategories as $key => $subcategory){
                $subcategories[$key] = levelStatus($subcategory);
            }
        }

        $category = Category::active()->find($categoryId);

        if (count($subcategories) == 0) {
            $category = Category::active()->where('id', $categoryId)
                ->questionCount('general')
                ->with(['quizInfos' => function ($quizInfo) {
                    $quizInfo->quizInfoLists('general');
                }, 'quizInfos.level', 'quizInfos.playInfo' => function ($q) {
                    $q->where('user_id', auth()->id());
                }])
                ->first();
            if($category){
                $category = levelStatus($category);
            }
        }

        $notify[] = 'Subcategory list';
        return response()->json([
            'remark'    => 'general_quiz_subcategory',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'category'                  => $category,
                'subcategories'             => $subcategories,
                'subcategory_image_path'    => asset(getFilePath('subcategory')),
                'category_image_path'       => asset(getFilePath('category'))
            ]
        ]);
    }

    public function questionList($quizInfoId)
    {
        $quizInfo = QuizInfo::active()->where('id', $quizInfoId)->with(['playInfo' => function ($q) {
            $q->where('user_id', auth()->id());
        }, 'level'])->first();

        $quizInfoPrev = QuizInfo::where('type_id', $quizInfo->type_id)
            ->where('category_id', $quizInfo->category_id)
            ->where('sub_category_id', @$quizInfo->sub_category_id)
            ->whereHas('level', function ($q) use ($quizInfo) {
                $q->where('level', $quizInfo->level->level - 1);
            })
            ->whereHas('playInfo', function ($q) {
                $q->where('user_id', auth()->id());
            })->first();

        if (!$quizInfoPrev && $quizInfo->level->level != 1) {
            $notify[] = 'Complete previous level first';
            return response()->json([
                'remark'    => 'general_quiz_question_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        if (!$quizInfo) {
            $notify[] = 'General quiz not found';
            return response()->json([
                'remark'    => 'general_quiz_question_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }
        $general = gs();
        $questions = $quizInfo->questions()->with('options')->inRandomOrder()->limit($general->per_level_question + 1)->get();

        $notify[] = '';
        return response()->json([
            'remark'    => 'general_quiz_question_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'quizInfo'                      => $quizInfo,
                'questions'                     => $questions,
                'per_question_answer_duration'  => $general->gq_ans_duration,
                'question_image_path'           => asset(getFilePath('question')),
            ]
        ]);
    }

    public function answerStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id.*'     => 'nullable',
            'question_id'     => 'required',
            'quizInfo_id'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'    => 'validation_error',
                'status'    => 'error',
                'message'   => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        $correctCount = 0;

        foreach ($request->question_id as $id) {
            $question = Question::find($id);
            $question->played_audience += 1;
            $question->save();

            $optionId = 'option_' . $id;
            if ($request->$optionId) {
                $option = QuestionOption::find(@$request->$optionId);
                $option->audience += 1;
                $option->save();
                if (@$option->is_answer) {
                    $correctCount++;
                }
            }
        }

        $currentLevelQuizInfo = QuizInfo::with('level')->with(['category', 'subcategory'])->find($request->quizInfo_id);

        $playInfo = PlayInfo::where('quiz_info_id', $request->quizInfo_id)->withCount(['playQuestions' => function ($q) {
            $q->where('is_correct', 1);
        }])->first();

        $general = gs();

        if (@$playInfo) {
            $user->score += (abs($correctCount - $playInfo->play_questions_count) * $general->gq_score);
        } else {
            $user->score += ($correctCount * $general->gq_score);
        }
        $user->save();

        if (floor(count($request->question_id) * ($currentLevelQuizInfo->winning_mark / 100)) <= $correctCount) {
            $playInfo = $this->storePlayInfo($request, 1);
            $this->storePlayQuestion($request, $playInfo);

            $nextLevelQuizInfo = QuizInfo::where('type_id', $currentLevelQuizInfo->type_id)
                ->with(['category', 'subcategory'])
                ->where('category_id', $currentLevelQuizInfo->category_id)
                ->where('sub_category_id', @$currentLevelQuizInfo->sub_category_id)
                ->whereHas('level', function ($query) use ($currentLevelQuizInfo) {
                    $query->where('level', $currentLevelQuizInfo->level->level + 1);
                })
                ->with('level')->first();

            $notify[] = 'Congratulations';
            return response()->json([
                'remark'    => 'general_quiz_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingScore'       => $correctCount,
                    'totalScore'        => $user->score,
                    'nextLevelQuizInfo' => $nextLevelQuizInfo
                ]
            ]);
        } else {
            $playInfo = $this->storePlayInfo($request, 0);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
            return response()->json([
                'remark'    => 'general_quiz_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingScore'       => $correctCount,
                    'totalScore'        => $user->score,
                    'thisLevelQuizInfo' => $currentLevelQuizInfo
                ]
            ]);
        }
    }

    protected function storePlayInfo($request, $is_win)
    {
        $user = auth()->user();
        $playInfo = PlayInfo::where('user_id', $user->id)->where('quiz_info_id', $request->quizInfo_id)->first();

        if (!$playInfo) {
            $playInfo                   = new PlayInfo();
            $playInfo->user_id          = $user->id;
            $playInfo->quiz_info_id     = $request->quizInfo_id;
        }
        $playInfo->is_win           = $is_win;
        $playInfo->fifty_fifty      = $request->fifty_fifty ? 0 : 1;
        $playInfo->audience_poll    = $request->audience_poll ? 0 : 1;
        $playInfo->time_reset       = $request->time_reset ? 0 : 1;
        $playInfo->flip_question    = $request->flip_question ? 0 : 1;
        $playInfo->save();
        return $playInfo;
    }

    protected function storePlayQuestion($request, $playInfo)
    {
        foreach ($request->question_id as $id) {
            $optionId = 'option_' . $id;
            if($request->$optionId){
                $option = QuestionOption::find($request->$optionId);

                $playQuestion = PlayQuestion::where('user_id', $playInfo->user_id)->where('play_info_id', $playInfo->id)->where('question_id', $id)->first();

                if (!$playQuestion) {
                    $playQuestion = new PlayQuestion();
                    $playQuestion->play_info_id = $playInfo->id;
                    $playQuestion->question_id = $id;
                    $playQuestion->user_id = $playInfo->user_id;
                }
                $playQuestion->options_id = $option->id;
                if (@$option->is_answer == 1) {
                    $playQuestion->is_correct = Status::YES;
                } else {
                    $playQuestion->is_correct = Status::NO;
                }
                $playQuestion->save();
            }
        }
    }
}
