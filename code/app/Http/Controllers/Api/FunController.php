<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\PlayInfo;
use App\Models\QuizInfo;
use App\Constants\Status;
use App\Models\SubCategory;
use App\Models\PlayQuestion;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FunController extends Controller
{
    public function funCategoryList()
    {
        $categories = Category::active()
            ->whereHas('quizInfos', function ($q) {
                $q->active()->typeCheck('fun');
            })
            ->subcategoryCount('fun')
            ->quizInfoCount('fun')
            ->get();

        if (count($categories) == 0) {
            $notify[] = 'Category list empty';
        }else{
            $notify[] = 'Category list';
        }

        return response()->json([
            'remark'    => 'fun_category_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'categories' => $categories,
                'category_image_path' => asset(getFilePath('category'))
            ]
        ]);
    }

    public function funSubcategoryList($categoryId)
    {
        $subcategories = SubCategory::active()->where('category_id', $categoryId)
            ->whereHas('quizInfos', function ($q) {
                $q->active()->typeCheck('fun');
            })
            ->quizInfoCount('fun')
            ->get();

        if (count($subcategories) == 0) {
            $notify[] = 'Subcategory list empty';
        }else{
            $notify[] = 'Subcategory list';
        }

        return response()->json([
            'remark'    => 'fun_subcategory_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'subcategories' => $subcategories,
                'subcategory_image_path' => asset(getFilePath('subcategory'))
            ]
        ]);
    }

    public function funList($categoryId, $subcategoryId = 0)
    {
        if ($subcategoryId == 0) {
            $funList = QuizInfo::active()->where('category_id', $categoryId)
                ->typeCheck('fun')
                ->withCount('questions')
                ->get();
        } else {
            $funList = QuizInfo::active()->where('category_id', $categoryId)->where('sub_category_id', $subcategoryId)
                ->whereHas('type', function ($q) {
                    $q->where('act', 'fun')->active();
                })
                ->withCount('questions')
                ->get();
        }

        if (count($funList) == 0) {
            $notify[] = 'Fun list empty';
            return response()->json([
                'remark'    => 'fun_list',
                'status'    => 'error',
                'message'   => ['error' => $notify],
            ]);
        }

        $notify[] = '';
        return response()->json([
            'remark'    => 'fun_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'funList' => $funList
            ]
        ]);
    }

    public function questionList($quizInfoId)
    {
        $quizInfo = QuizInfo::where('id', $quizInfoId)->with('playInfo', function ($q) {
            $q->where('user_id', auth()->id());
        })->first();

        $user = auth()->user();
        if ($user->coins > 0 && ($user->coins - $quizInfo->point) >= 0) {
            if (!$quizInfo) {
                $notify[] = 'Page not found';
                return response()->json([
                    'remark'    => 'question_list',
                    'status'    => 'error',
                    'message'   => ['error' => $notify],
                ]);
            }

            $questions = $quizInfo->questions()->with('options')->get();

            if (!$quizInfo->playInfo) {
                $user = auth()->user();
                $user->coins -= $quizInfo->point;
                $user->save();
            }

            $general = gs();

            $notify[] = '';
            return response()->json([
                'remark'    => 'fun_list',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'quizInfo' => $quizInfo,
                    'questions' => $questions,
                    'fun_ans_duration' => $general->fun_ans_duration,
                    'question_image_path' => asset(getFilePath('question'))
                ]
            ]);
        } else {
            $notify[] = 'Do not have sufficient coin';
            return response()->json([
                'remark'    => 'fun_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }
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
            $optionId = 'option_' . $id;
            if ($request->$optionId) {
                $option = QuestionOption::find($request->$optionId);
                if($option){
                    $option->audience += 1;
                    $option->save();
                    if (@$option->is_answer == Status::YES) {
                        $correctCount++;
                    }
                }

            }
        }

        $quizInfo = QuizInfo::with('level')->find($request->quizInfo_id);

        $playInfo = PlayInfo::where('quiz_info_id', $request->quizInfo_id)->withCount(['playQuestions' => function ($q) {
            $q->where('is_correct', 1);
        }])->first();

        $general = gs();

        if (floor(count($request->question_id) * ($quizInfo->winning_mark / 100)) <= $correctCount) {
            if (@$playInfo) {
                $user->score += (abs($correctCount - $playInfo->play_questions_count) * $general->fun_score);
                if ($playInfo->is_win == 0) {
                    $user->coins += $quizInfo->prize;
                }
            } else {
                $user->coins += $quizInfo->prize;
                $user->score += ($correctCount * $general->fun_score);
            }
            $user->save();

            $playInfo = $this->storePlayInfo($request, 1);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
        } else {
            if (@$playInfo) {
                $user->score += (abs($correctCount - $playInfo->play_questions_count) * $general->fun_score);
            } else {
                $user->score += ($correctCount * $general->fun_score);
            }
            $user->save();

            $playInfo = $this->storePlayInfo($request, 0);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
        }

        return response()->json([
            'remark'    => 'fun_store_answer',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'totalQuestion'     => count($request->question_id),
                'correctAnswer'     => $correctCount,
                'wrongAnswer'       => count($request->question_id) - $correctCount,
                'winingScore'        => $correctCount,
                'totalScore'         => $user->score
            ]
        ]);
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
        $playInfo->save();
        return $playInfo;
    }

    protected function storePlayQuestion($request, $playInfo)
    {
        foreach ($request->question_id as $id) {
            $optionId = 'option_' . $id;
            if ($request->$optionId) {
                $option = QuestionOption::find($request->$optionId);

                $playQuestion = PlayQuestion::where('user_id', $playInfo->user_id)->where('play_info_id', $playInfo->id)->where('question_id', $id)->first();

                if (!$playQuestion) {
                    $playQuestion = new PlayQuestion();
                    $playQuestion->play_info_id     = $playInfo->id;
                    $playQuestion->question_id      = $id;
                    $playQuestion->user_id          = $playInfo->user_id;
                }
                $playQuestion->options_id       = $optionId;

                if (@$option->is_answer == Status::YES) {
                    $playQuestion->is_correct = Status::YES;
                } else {
                    $playQuestion->is_correct = Status::YES;
                }
                $playQuestion->save();
            }
        }
    }
}
