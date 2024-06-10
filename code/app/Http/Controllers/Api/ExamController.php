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
use Carbon\Carbon;

class ExamController extends Controller
{
    public function examList()
    {
        $exams = QuizInfo::active()
            ->whereDate('start_date', '>=', today())
            ->whereTime('exam_end_time', '>=', now()->format('H:i:s'))
            ->whereHas('type', function ($q) {
                $q->where('act', 'exam')->active();
            })->with('playInfo', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->orderBy('id', 'desc')
            ->get();

        $notify[] = '';
        return response()->json([
            'remark'    => 'exam_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'exams' => $exams,
                'exam_image_path' => asset(getFilePath('exam'))
            ]
        ]);
    }

    public function examDetails($id)
    {
        $exam = QuizInfo::active()
            ->where('id', $id)
            ->whereDate('start_date', '>=', today())
            ->whereTime('exam_end_time', '>=', now()->format('H:i:s'))
            ->whereHas('type', function ($q) {
                $q->where('act', 'exam')->active();
            })
            ->with(['playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }, 'playinfo.playQuestions'])
            ->first();

        if (!$exam) {
            $notify[] = 'Exam not found';
            return response()->json([
                'remark'    => 'exam_details',
                'status'    => 'error',
                'message'   => ['error' => $notify],
            ]);
        }

        if ($exam->playInfo) {
            $notify[] = 'You have already finished this exam.';
            return response()->json([
                'remark'    => 'exam_details',
                'status'    => 'success',
                'message'   => ['success' => $notify],
            ]);
        }

        $notify[] = '';
        return response()->json([
            'remark'    => 'exam_details',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'exam' => $exam,
                'exam_image_path' => asset(getFilePath('exam')),
                'question_image_path' => asset(getFilePath('question')),
            ]
        ]);
    }

    public function examQuestionList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_key'          => 'required',
            'quizInfo_id'       => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'    => 'validation_error',
                'status'    => 'error',
                'message'   => ['error' => $validator->errors()->all()],
            ]);
        }

        $exam = QuizInfo::where('id', $request->quizInfo_id)
            ->with('playInfo', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->first();

        $user = auth()->user();

        if (Carbon::parse($exam->start_date)->isToday()) {
            if (Carbon::parse($exam->exam_end_time)->gte(Carbon::now())) {
                if (Carbon::parse($exam->exam_start_time)->lte(Carbon::now())) {
                    if ($user->coins > 0 && ($user->coins - $exam->point) >= 0) {
                        if ($exam->playInfo) {
                            $notify[] = 'You have already finished this exam.';
                            return response()->json([
                                'remark'    => 'exam_question_list',
                                'status'    => 'success',
                                'message'   => ['success' => $notify],
                            ]);
                        }

                        if ($exam->exam_key != $request->exam_key) {
                            $notify[] = 'Exam key is not valid';
                            return response()->json([
                                'remark'    => 'exam_question_list',
                                'status'    => 'error',
                                'message'   => ['error' => $notify],
                            ]);
                        }

                        $playInfo = new PlayInfo();
                        $playInfo->user_id = auth()->id();
                        $playInfo->quiz_info_id = $request->quizInfo_id;
                        $playInfo->save();

                        $user = auth()->user();
                        $user->coins -= $exam->point;
                        $user->save();

                        $questions = $exam->questions()->with('options')->get();

                        $notify[] = '';
                        return response()->json([
                            'remark'    => 'exam_question_list',
                            'status'    => 'success',
                            'message'   => ['success' => $notify],
                            'data'      => [
                                'exam'                  => $exam,
                                'questions'             => $questions,
                                'question_image_path'   => asset(getFilePath('question'))
                            ]
                        ]);
                    } else {
                        $notify[] = 'Do not have sufficient coin';
                        return response()->json([
                            'remark'    => 'exam_question_list',
                            'status'    => 'error',
                            'message'   => ['error' => $notify]
                        ]);
                    }
                } else {
                    $notify[] = 'Exam will be start soon.';
                    return response()->json([
                        'remark'    => 'exam_question_list',
                        'status'    => 'success',
                        'message'   => ['success' => $notify],
                    ]);
                }
            } else {
                $notify[] = 'Exam is closed.';
                return response()->json([
                    'remark'    => 'exam_question_list',
                    'status'    => 'success',
                    'message'   => ['success' => $notify],
                ]);
            }
        } elseif (Carbon::parse($exam->start_date)->isFuture()) {
            $notify[] = 'Exam will be start soon';
            return response()->json([
                'remark'    => 'exam_question_list',
                'status'    => 'success',
                'message'   => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'Exam is closed';
            return response()->json([
                'remark'    => 'exam_question_list',
                'status'    => 'success',
                'message'   => ['success' => $notify],
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
                $option = QuestionOption::find(@$request->$optionId);
                $option->audience += 1;
                $option->save();
                if (@$option->is_answer) {
                    $correctCount++;
                }
            }
        }

        $quizInfo = QuizInfo::where('id', $request->quizInfo_id)
            ->with('playInfo', function ($q) {
                $q->where('user_id', auth()->id());
            })->first();

        $playInfo = $quizInfo->playInfo;
        $general = gs();

        if (floor(count($request->question_id) * ($quizInfo->winning_mark / 100)) <= $correctCount) {
            $user->score += ($correctCount * $general->exam_score);
            $user->coins += $quizInfo->prize;
            $user->save();

            $playInfo->is_win = 1;
            $playInfo->save();

            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Congratulations';
            return response()->json([
                'remark'    => 'exam_store_answer',
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
            $user->score += ($correctCount * $general->exam_score);
            $user->save();

            $playInfo->is_win = 0;
            $playInfo->save();

            $this->storePlayQuestion($request, $playInfo);

            $notify[] = 'Failed';
            return response()->json([
                'remark'    => 'exam_store_answer',
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

    public function examCode($id)
    {
        $exam = QuizInfo::find($id);
        $user     = auth()->user();

        $shortCode = [
            'title' => $exam->title,
            'code' => $exam->exam_key
        ];

        notify($user, 'SEND_LIVE_EXAM_CODE', $shortCode);

        $notify[] = 'Successfully send exam code';
        return response()->json([
            'remark'    => '',
            'status'    => 'success',
            'message'   => ['success' => $notify]
        ]);
    }

    public function completeExam()
    {
        $completedExams = QuizInfo::active()
            ->withCount('questions')
            ->whereHas('playInfo', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereHas('type', function ($q) {
                $q->where('act', 'exam')->active();
            })
            ->with(['playInfo' => function ($q) {
                $q->where('user_id', auth()->id());
            }, 'playInfo.playQuestions'])
            ->get();


        $notify[] = '';
        return response()->json([
            'remark'    => 'completed_exam_list',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'exams' => $completedExams,
                'exam_image_path' => asset(getFilePath('exam'))
            ]
        ]);
    }
}
