<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use App\Models\User;
use App\Models\Category;
use App\Models\QuizInfo;
use App\Models\DeviceToken;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Constants\Status;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $type = Type::active()->where('act', '<>', 'contest')->where('act', '<>', 'exam')->where('act', '<>', 'general')->get();

        $categories = Category::active()
            ->withCount(['questions' => function ($q) {
                $q->whereHas('quizInfos', function ($query) {
                    $query->whereHas('type', function ($qu) {
                        $qu->where('act', 'general');
                    });
                });
            }])
            ->whereHas('subcategories')
            ->limit(6)->get();

        $contest = QuizInfo::whereDate('start_date', '>=', today())
            ->orWhereDate('end_date', '>=', today())
            ->active()
            ->whereHas('type', function ($q) {
                $q->where('act', 'contest')->active();
            })
            ->limit(4)
            ->get();

        $exams = QuizInfo::active()
            ->whereDate('start_date', '>=', today())
            ->whereDate('exam_end_time', '>', now()->format('H:i'))
            ->whereHas('type', function ($q) {
                $q->active()->where('act', 'exam');
            })
            ->limit(4)
            ->orderBy('id', 'desc')->get();

        $rank = User::where('id', auth()->id())->selectRaw('(SELECT COUNT(DISTINCT score) FROM users u2 WHERE u2.score >= users.score) AS user_rank')->first();

        $quizList = Type::select('act', 'status')->get();

        $notify[] = '';
        return response()->json([
            'remark'    => 'dashboard',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'user'                  => $user,
                'rank'                  => $rank,
                'quiz_type'             => $type,
                'categories'            => $categories,
                'contest'               => $contest,
                'exams'                 => $exams,
                'category_image_path'   => asset(getFilePath('category')),
                'contest_image_path'    => asset(getFilePath('contest')),
                'quiz_image_path'       => asset(getFilePath('quiz')),
                'exam_image_path'       => asset(getFilePath('exam')),
                'user_image_path'       => asset(getFilePath('userProfile')),
                'general_quiz_status' => $quizList[0]->status,
                'contest_status' => $quizList[1]->status,
                'fun_n_learn_status' => $quizList[2]->status,
                'guess_the_word_status' => $quizList[3]->status,
                'exam_status' => $quizList[4]->status,
                'daily_quiz_status' => $quizList[5]->status,
                'single_battle_status' => $quizList[6]->status,
            ]
        ]);
    }

    public function userDetails()
    {
        $user = auth()->user();
        $rank = User::where('id', auth()->id())->selectRaw('(SELECT COUNT(DISTINCT score) FROM users u2 WHERE u2.score >= users.score) AS user_rank')->first();

        $notify[] = 'User information';
        return response()->json([
            'remark'    => 'user_info',
            'status'    => 'success',
            'message'   => ['success' => $notify],
            'data'      => [
                'user'      => $user,
                'rank'      => $rank
            ]
        ]);
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete == 1) {
            $notify[] = 'You\'ve already completed your profile';
            return response()->json([
                'remark'    => 'already_completed',
                'status'    => 'error',
                'message'   => ['error' => $notify],
            ]);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'    => 'validation_error',
                'status'    => 'error',
                'message'   => ['error' => $validator->errors()->all()],
            ]);
        }

        $user->firstname        = $request->firstname;
        $user->lastname         = $request->lastname;
        $user->profile_complete = 1;
        $user->address          = [
            'country'   => @$user->address->country,
            'address'   => $request->address,
            'state'     => $request->state,
            'zip'       => $request->zip,
            'city'      => $request->city,
        ];
        $user->save();

        $notify[] = 'Profile completed successfully';
        return response()->json([
            'remark'    => 'profile_completed',
            'status'    => 'success',
            'message'   => ['success' => $notify],
        ]);
    }

    public function depositHistory(Request $request)
    {
        $deposits = auth()->user()->deposits();
        if ($request->search) {
            $deposits = $deposits->where('trx', $request->search);
        }
        $deposits = $deposits->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[] = 'Deposit data';
        return response()->json([
            'remark' => 'deposits',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'deposits' => $deposits
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $remarks = Transaction::distinct('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }


        if ($request->type) {
            $type = $request->type == 'plus' ? '+' : '-';
            $transactions = $transactions->where('trx_type', $type);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[] = 'Transactions data';
        return response()->json([
            'remark' => 'transactions',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'transactions' => $transactions,
                'remarks' => $remarks,
            ]
        ]);
    }

    public function submitProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'avatar' => ['image', new FileTypeValidate(['png', 'jpeg', 'jpg'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            try {
                $user->avatar = fileUploader($request->avatar, getFilePath('userProfile'), getFileSize('userProfile'), @$user->avatar);
            } catch (\Exception $exp) {
                throw ValidationException::withMessages(['error' => 'Couldn\'t upload your image']);
            }
        }

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'city' => $request->city,
        ];
        $user->save();

        $notify[] = 'Profile updated successfully';
        return response()->json([
            'remark' => 'profile_updated',
            'status' => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function submitPassword(Request $request)
    {
        $passwordValidation = Password::min(6);
        $general = GeneralSetting::first();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = 'Password changed successfully';
            return response()->json([
                'remark' => 'password_changed',
                'status' => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }

    public function profileAvatarUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', new FileTypeValidate(['png', 'jpeg', 'jpg'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            try {
                $user->avatar = fileUploader($request->avatar, getFilePath('userProfile'), getFileSize('userProfile'), @$user->avatar);
            } catch (\Exception $exp) {
                throw ValidationException::withMessages(['error' => 'Couldn\'t upload your image']);
            }
        }
        $user->save();

        $notify[] = 'Avatar update successfully';
        return response()->json([
            'remark' => 'avatar_changed',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'user' => $user
            ]
        ]);
    }

    public function leaderBoard()
    {
        $allTimeRank = User::select(['username', 'firstname', 'lastname', 'score', 'avatar'])->selectRaw('(SELECT COUNT(DISTINCT score) FROM users u2 WHERE u2.score >= users.score) AS user_rank')->limit(100)->orderBy('user_rank', 'asc')->get();

        $notify[] = '';
        return response()->json([
            'remark' => 'leader_board',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'user' => $allTimeRank,
                'user_avatar_path' => asset(getFilePath('userProfile'))
            ]
        ]);
    }

    public function getDeviceToken(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();
        if ($deviceToken) {
            $notify[] = 'Already exists';
            return response()->json([
                'remark'  => 'get_device_token',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->id();
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = 1;
        $deviceToken->save();

        $notify[] = 'Token save successfully';
        return response()->json([
            'remark'  => 'get_device_token',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function deleteUser()
    {
        $user = auth()->user();
        $user->delete_status = Status::YES;
        $user->save();

        auth()->user()->tokens()->delete();

        $notify[] = 'Account deleted successfully';
        return response()->json([
            'remark'  => 'account_delete',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }
}
