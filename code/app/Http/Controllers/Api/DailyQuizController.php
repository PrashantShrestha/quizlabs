<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\PlayInfo;
use App\Models\QuizInfo;
use App\Constants\Status;
use App\Models\PlayQuestion;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DailyQuizController extends Controller
{
    public function questionList()
    {
        $dailyQuiz = QuizInfo::active()
            ->where('title', Carbon::parse(today())->format('Y-m-d'))
            ->typeCheck('daily_quiz')
            ->with(['playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
            ->first();

        if (!$dailyQuiz) {
            $notify[] = 'Daily quiz not found';
            return response()->json([
                'remark'    => 'daily_quiz',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        $user = auth()->user();
        if ($user->coins > 0 && ($user->coins - $dailyQuiz->point) >= 0) {
            if (!$dailyQuiz) {
                $notify[] = 'Daily quiz not found';
                return response()->json([
                    'remark'    => 'daily_quiz',
                    'status'    => 'error',
                    'message'   => ['error' => $notify]
                ]);
            }

            if ($dailyQuiz->playInfo) {
                $notify[] = 'You have already played daily quiz';
                return response()->json([
                    'remark'    => 'daily_quiz',
                    'status'    => 'error',
                    'message'   => ['error' => $notify],
                    'data'      => [
                        'dailyQuiz' => $dailyQuiz
                    ]
                ]);
            }

            $playInfo = new PlayInfo();
            $playInfo->quiz_info_id = $dailyQuiz->id;
            $playInfo->user_id = auth()->id();
            $playInfo->save();

            $user = auth()->user();
            $user->coins -= $dailyQuiz->point;
            $user->save();

            $general = gs();
            $questions = $dailyQuiz->questions()->with('options')->get();

            $notify[] = '';
            return response()->json([
                'remark'    => 'daily_quiz',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'dailyQuiz'                 => $dailyQuiz,
                    'questions'                 => $questions,
                    'daily_quiz_ans_duration'   => $general->daily_quiz_ans_duration,
                    'question_image_path'       => asset(getFilePath('question'))
                ]
            ]);
        } else {
            $notify[] = 'Do not have sufficient coin';
            return response()->json([
                'remark'    => 'daily_quiz',
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
                if ($option) {
                    $option->audience += 1;
                    $option->save();

                    if (@$option->is_answer == Status::YES) {
                        $correctCount++;
                    }
                }
            }
        }

        $quizInfo = QuizInfo::find($request->quizInfo_id);
        $general = gs();

        if (floor(count($request->question_id) * ($quizInfo->winning_mark / 100)) <= $correctCount) {
            $user->score += ($correctCount * $general->daily_quiz_score);
            $user->coins += $quizInfo->prize;
            $user->save();

            $playInfo = $this->storePlayInfo($request, 1);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
            return response()->json([
                'remark'    => 'daily_quiz_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingCoin'        => $quizInfo->prize,
                    'user'              => $user
                ]
            ]);
        } else {
            $user->score += ($correctCount * $general->daily_quiz_score);
            $user->save();

            $playInfo = $this->storePlayInfo($request, 0);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
            return response()->json([
                'remark'    => 'daily_quiz_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingCoin'        => 0,
                    'user'              => $user

                ]
            ]);
        }
    }

    protected function storePlayInfo($request, $is_win)
    {
        $user = auth()->user();
        $playInfo = PlayInfo::where('quiz_info_id', $request->quizInfo_id)
            ->where('user_id', auth()->id())
            ->withCount(['playQuestions' => function ($q) {
                $q->where('is_correct', 1)->where('user_id', auth()->id());
            }])->first();

        if (!$playInfo) {
            $playInfo                   = new PlayInfo();
        }
        $playInfo->user_id          = $user->id;
        $playInfo->quiz_info_id     = $request->quizInfo_id;
        $playInfo->is_win           = $is_win;
        $playInfo->fifty_fifty      = $request->fifty_fifty ? 1 : 0;
        $playInfo->audience_poll    = $request->audience_poll ? 1 : 0;
        $playInfo->time_reset       = $request->time_reset ? 1 : 0;
        $playInfo->flip_question    = $request->flip_question ? 1 : 0;
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
                $playQuestion->options_id       = $optionId;
                if (@$option->is_answer == Status::YES) {
                    $playQuestion->is_correct = Status::YES;
                } else {
                    $playQuestion->is_correct = Status::NO;
                }
                $playQuestion->save();
            }
        }
    }
}
