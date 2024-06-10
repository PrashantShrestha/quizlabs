<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\PlayInfo;
use App\Models\Question;
use App\Constants\Status;
use App\Models\PlayQuestion;
use App\Models\SingleBattle;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class SingleBattleController extends Controller
{
    public function singleBattle()
    {
        $categories = null;

        $general = gs();
        if ($general->battle_category) {
            $categories = Category::active()->get();
        }

        $entryCoin = $general->battle_participate_point;

        $notify[] = '';
        return response()->json([
            'remark'    => 'single_battle',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'categories'    => $categories,
                'entryCoin'     => $entryCoin
            ]
        ]);
    }

    public function questionList($userId = null, $opponentId = null, $categoryId = null)
    {
        $general = gs();

        $this->createBattle($userId, $opponentId, $categoryId);
        $this->createBattle($opponentId, $userId,  $categoryId);

        $user = auth()->user();

        if ($user->coins > 0 && ($user->coins > $general->battle_participate_point)) {
            $questions = Question::active()
                ->whereHas('quizInfos', function ($q) use ($categoryId) {
                    $q->where('category_id', @$categoryId);
                })
                ->whereDoesntHave('quizInfos', function ($q) {
                    $q->whereHas('type', function ($query) {
                        $query->where('act', 'guess_word');
                    });
                })
                ->inRandomOrder()
                ->with('options')
                ->limit($general->per_battle_question)
                ->orderBy('id', 'desc')
                ->get();

            $notify[] = '';
            return response()->json([
                'remark'    => 'single_battle_question_list',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'questions'             => $questions,
                    'question_image_path'   => asset(getFilePath('question')),
                ]
            ]);
        } else {
            $notify[] = 'Do not have sufficient coin';
            return response()->json([
                'remark'    => 'single_battle_question_list',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }
    }

    public function answerStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id.*' => 'nullable',
            'question_id' => 'required',
            'opponent_id'    => 'required',
            'coin_count' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'    => 'validation_error',
                'status'    => 'error',
                'message'   => ['error' => $validator->errors()->all()],
            ]);
        }

        if (auth()->id() == $request->opponent_id) {
            $notify[] = 'Opponent must be defferent user';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        $myBattle = SingleBattle::where('user_id', auth()->id())->where('opponent_id', $request->opponent_id)->where('status', 0)->first();

        if (!$myBattle) {
            $notify[] = 'Create a battle first';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        $myCorrectCount = 0;
        $opponentCorrectCount = 0;

        foreach ($request->question_id as $id) {
            $optionId = 'my_option_' . $id;
            if ($request->$optionId) {
                $option = QuestionOption::find($request->$optionId);
                @$option->audience += 1;
                @$option->save();
                if (@$option->is_answer) {
                    $myCorrectCount += 1;
                }
            }

            $opponentOptionId = 'opponent_option_' . $id;
            if ($request->$opponentOptionId) {
                $option = QuestionOption::find($request->$opponentOptionId);
                @$option->audience += 1;
                @$option->save();
                if (@$option->is_answer) {
                    $opponentCorrectCount += 1;
                }
            }
        }
        $general = gs();
        $user = auth()->user();
        $opponent = User::find($request->opponent_id);

        if (!$opponent) {
            $notify[] = 'Opponent not found';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        if ($myCorrectCount > $opponentCorrectCount) {
            $user->coins += ($general->battle_participate_point);
            $user->save();

            $myBattle->winner_id = $user->id;
            $myBattle->status = 1;
            $myBattle->save();
            $playInfo = $this->storePlayInfo($myBattle, 1);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $myCorrectCount,
                    'wrongAnswer'       => count($request->question_id) - $myCorrectCount,
                    'winingScore'       => $myCorrectCount,
                    'winning_coin'      => $general->battle_participate_point * 2,
                    'opponent'          => $opponent,
                    'opponent_correct_ans' => $opponentCorrectCount
                ]
            ]);
        } elseif ($myCorrectCount == $opponentCorrectCount) {
            $myBattle->status = 1;
            $myBattle->save();
            $playInfo = $this->storePlayInfo($myBattle, 2);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Draw';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $myCorrectCount,
                    'wrongAnswer'       => count($request->question_id) - $myCorrectCount,
                    'winingScore'       => $myCorrectCount,
                    'winning_coin'      => 0,
                    'opponent'          => $opponent,
                    'opponent_correct_ans' => $opponentCorrectCount
                ]
            ]);
        } else {
            $user->coins -= ($general->battle_participate_point);
            $user->save();
            $myBattle->winner_id = $opponent->id;
            $myBattle->status = 1;
            $myBattle->save();
            $playInfo = $this->storePlayInfo($myBattle, 0);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
            return response()->json([
                'remark'    => 'single_battle_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $myCorrectCount,
                    'wrongAnswer'       => count($request->question_id) - $myCorrectCount,
                    'winingScore'       => $myCorrectCount,
                    'loosing_coin'      => $general->battle_participate_point,
                    'opponent'          => $opponent,
                    'opponent_correct_ans' => $opponentCorrectCount
                ]
            ]);
        }
    }

    protected function storePlayInfo($myBattle, $is_win)
    {
        $user = auth()->user();
        $playInfo                   = new PlayInfo();
        $playInfo->user_id          = $user->id;
        $playInfo->single_battle_id = $myBattle->id;
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

                $playQuestion = new PlayQuestion();
                $playQuestion->play_info_id     = $playInfo->id;
                $playQuestion->question_id      = $id;
                $playQuestion->user_id          = $playInfo->user_id;
                $playQuestion->options_id       = $option->id;

                if (@$option->is_answer) {
                    $playQuestion->is_correct       = Status::YES;
                }
                $playQuestion->save();
            }
        }
    }

    protected function createBattle($userId, $opponentId, $categoryId)
    {
        $battle = new SingleBattle();
        $battle->user_id = $userId;
        $battle->opponent_id = $opponentId;
        $battle->category_id = $categoryId;
        $battle->save();
    }
}
