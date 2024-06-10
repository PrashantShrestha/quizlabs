<?php

namespace App\Http\Controllers\Api;

use App\Models\PlayInfo;
use App\Models\QuizInfo;
use App\Constants\Status;
use App\Models\PlayQuestion;
use Illuminate\Http\Request;
use App\Models\QuestionOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ContestController extends Controller
{
    public function contestList()
    {
        $contests = QuizInfo::active()
            ->typeCheck('contest')
            ->where(function ($q) {
                $q->whereDate('start_date', '>=', today())->orWhereDate('end_date', '>=', today());
            })->orderBy('start_date', 'asc')
            ->questionCount('contest')
            ->get();

        $notify[] = '';
        return response()->json([
            'remark'    => 'contest',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'contests'              => $contests,
                'contest_image_path'    => asset(getFilePath('contest'))
            ]
        ]);
    }

    public function questionList($contestId)
    {
        $contest = QuizInfo::where('id', $contestId)
            ->typeCheck('contest')
            ->with(['playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
            ->first();

        if (!$contest) {
            $notify[] = 'Contest Not Found';
            return response()->json([
                'remark'    => 'contest',
                'status'    => 'error',
                'message'   => ['error' => $notify]
            ]);
        }

        $user = auth()->user();
        if ($user->coins > 0 && ($user->coins - $contest->point) >= 0) {
            $user = auth()->user();
            $user->coins -= $contest->point;
            $user->save();

            $playInfo               = new PlayInfo();
            $playInfo->user_id      = $user->id;
            $playInfo->quiz_info_id = $contest->id;
            $playInfo->save();

            $general = gs();
            $questions = $contest->questions()->with('options')->get();

            $notify[] = '';
            return response()->json([
                'remark'    => 'contest',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'contest'               => $contest,
                    'questions'             => $questions,
                    'contest_ans_duration'  => $general->contest_ans_duration,
                    'question_image_path'   => asset(getFilePath('question')),
                    'contest_image_path'    => asset(getFilePath('contest'))
                ]
            ]);
        } else {
            $notify[] = 'Do not have sufficient coin';
            return response()->json([
                'remark'    => 'contest',
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
                $option->audience += 1;
                $option->save();

                if (@$option->is_answer == Status::YES) {
                    $correctCount++;
                }
            }
        }

        $quizInfo = QuizInfo::find($request->quizInfo_id);
        $general = gs();

        if (floor(count($request->question_id) * ($quizInfo->winning_mark / 100)) <= $correctCount) {
            $user->score += ($correctCount * $general->contest_score);
            $user->coins += $quizInfo->prize;
            $user->save();

            $playInfo = $this->storePlayInfo($request, 1);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
            return response()->json([
                'remark'    => 'contest_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingCoin'        => $quizInfo->prize,
                    'totalCoin'         => $user->coins
                ]
            ]);
        } else {
            $user->score += ($correctCount * $general->contest_score);
            $user->save();

            $playInfo = $this->storePlayInfo($request, 0);
            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
            return response()->json([
                'remark'    => 'contest_store_answer',
                'status'    => 'success',
                'message'   => ['success' => $notify],
                'data'      => [
                    'totalQuestion'     => count($request->question_id),
                    'correctAnswer'     => $correctCount,
                    'wrongAnswer'       => count($request->question_id) - $correctCount,
                    'winingCoin'        => 0,
                    'totalCoin'         => $user->coins
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
            if ($request->$optionId) {
                $option = QuestionOption::find($request->$optionId);
                $playQuestion = new PlayQuestion();
                $playQuestion->play_info_id     = $playInfo->id;
                $playQuestion->question_id      = $id;
                $playQuestion->user_id          = $playInfo->user_id;
                $playQuestion->options_id       = $optionId;
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
