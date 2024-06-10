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

class GuessWordController extends Controller
{
    public function guessCategoryList()
    {
        $categories = Category::active()
            ->whereHas('quizInfos', function ($q) {
                $q->active()->typeCheck('guess_word');
            })
            ->subcategoryCount('guess_word')
            ->questionCount('guess_word')
            ->with(['quizInfos' => function ($q) {
                $q->quizInfoLists('guess_word');
            }, 'quizInfos.subcategory' => function ($q) {
                $q->active();
            }, 'quizInfos.playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }, 'quizInfos.level'])
            ->get();

        if (count($categories) == 0) {
            $notify[] = 'Category list empty';
        } else {
            foreach ($categories as $key => $category) {
                $categories[$key] = levelStatus($category);
            }
            $notify[] = 'Category list';
        }

        return response()->json([
            'remark'    => 'guess_word_category_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'categories' => $categories,
                'category_image_path' => asset(getFilePath('category'))
            ]
        ]);
    }

    public function guessSubcategoryList($categoryId)
    {
        $subcategories = SubCategory::active()->where('category_id', $categoryId)
            ->whereHas('quizInfos', function ($q) {
                $q->typeCheck('guess_word');
            })
            ->questionCount('guess_word')
            ->with(['quizInfos' => function ($q) {
                $q->quizInfoLists('guess_word');
            }, 'quizInfos.level', 'quizInfos.playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
            ->get();

        if (count($subcategories) == 0) {
            $notify[] = 'Subcategory list empty';
        } else {
            foreach ($subcategories as $key => $subcategory) {
                $subcategories[$key] = levelStatus($subcategory);
            }
            $notify[] = 'Subcategory list';
        }

        return response()->json([
            'remark'    => 'guess_word_subcategory_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'subcategories' => $subcategories,
                'subcategory_image_path' => asset(getFilePath('subcategory'))
            ]
        ]);
    }

    public function guessWordQuestionList($quizInfoId)
    {
        $quizInfo = QuizInfo::where('id', $quizInfoId)->with(['playInfo' => function ($q) {
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
                'remark'    => 'guess_word_question_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        if (!$quizInfo) {
            $notify[] = 'Page Not Found';
            return response()->json([
                'remark'    => 'guess_word_question_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }
        $general = gs();
        $questions = $quizInfo->questions()->with('options')->inRandomOrder()->limit($general->per_level_question + 1)->get();

        $notify[] = '';
        return response()->json([
            'remark'    => 'guess_word_question_list',
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
            $optionId = 'option_' . $id;
            $requestAnswer = $request->$optionId;

            $correctAnswer = QuestionOption::where('question_id', $id)->where('option', $requestAnswer)->first();
            if (@$correctAnswer) {
                $correctCount += 1;
            }
        }

        $playInfo = PlayInfo::where('quiz_info_id', $request->quizInfo_id)->where('user_id', auth()->id())->withCount(['playQuestions' => function ($q) {
            $q->where('is_correct', 1)->where('user_id', auth()->id());
        }])->first();

        $quizInfo = QuizInfo::find($request->quizInfo_id);

        $general = gs();

        if (floor(count($request->question_id) * ($quizInfo->winning_mark / 100)) <= $correctCount) {
            $is_win = 1;
            if (@$playInfo) {
                $user->score += (abs($correctCount - $playInfo->play_questions_count) * $general->guess_score);
            } else {
                $user->score += ($correctCount * $general->guess_score);
            }
            $user->save();

            $playInfo = $this->storePlayInfo($request, $is_win);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
        } else {
            $is_win = 0;
            if (@$playInfo) {
                if ($correctCount < $playInfo->play_questions_count) {
                    $user->score -= (abs($correctCount - $playInfo->play_questions_count) * $general->guess_score);
                } else {
                    $user->score += (abs($correctCount - $playInfo->play_questions_count) * $general->guess_score);
                }
            } else {
                $user->score += ($correctCount * $general->guess_score);
            }
            $user->save();

            $playInfo = $this->storePlayInfo($request, $is_win);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
        }

        return response()->json([
            'remark'    => 'guess_word_store_answer',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'totalQuestion'     => count($request->question_id),
                'correctAnswer'     => $correctCount,
                'wrongAnswer'       => count($request->question_id) - $correctCount,
                'winingScore'       => $correctCount,
                'totalScore'        => $user->score
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
            $requestAnswer = $request->$optionId;

            $playQuestion = PlayQuestion::where('user_id', $playInfo->user_id)->where('play_info_id', $playInfo->id)->where('question_id', $id)->first();
            $option = QuestionOption::where('question_id', $id)->first();
            if (!@$playQuestion) {
                $playQuestion = new PlayQuestion();
                $playQuestion->play_info_id     = $playInfo->id;
                $playQuestion->question_id      = $id;
                $playQuestion->user_id          = $playInfo->user_id;
            }
            $playQuestion->options_id           = [$option->id];
            if (@strtolower($option->option) == strtolower($requestAnswer)) {
                $playQuestion->is_correct       = Status::YES;
            } else {
                $playQuestion->is_correct       = Status::NO;
            }
            $playQuestion->save();
        }
    }
}
