<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PlayInfo;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ManageUsersController extends Controller
{

    public function allUsers()
    {
        $pageTitle = 'All Users';
        $users = $this->userData();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function activeUsers()
    {
        $pageTitle = 'Active Users';
        $users = $this->userData('active');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Users';
        $users = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified Users';
        $users = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified Users';
        $users = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileUnverifiedUsers()
    {
        $pageTitle = 'Mobile Unverified Users';
        $users = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileVerifiedUsers()
    {
        $pageTitle = 'Mobile Verified Users';
        $users = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function usersWithCoins()
    {
        $pageTitle = 'Users with Coins';
        $users = $this->userData('withCoins');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    protected function userData($scope = null)
    {
        if ($scope) {
            $users = User::$scope();
        } else {
            $users = User::query();
        }
        return $users->searchable(['username', 'email'])->orderBy('id', 'desc')->paginate(getPaginate());
    }


    public function detail($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Detail - ' . $user->username;

        $totalContest = PlayInfo::where('user_id', $user->id)
            ->whereHas('quizInfo', function ($q) {
                $q->whereHas('type', function ($query) {
                    $query->where('act', 'contest');
                });
            })
            ->count();

        $totalExam = PlayInfo::where('user_id', $user->id)
            ->whereHas('quizInfo', function ($q) {
                $q->whereHas('type', function ($query) {
                    $query->where('act', 'exam');
                });
            })
            ->count();

        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user', 'countries', 'totalContest', 'totalExam'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray   = (array)$countryData;
        $countries      = implode(',', array_keys($countryArray));

        $countryCode    = $request->country;
        $country        = $countryData->$countryCode->country;
        $dialCode       = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname' => 'required|string|max:40',
            'email' => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile' => 'required|string|max:40|unique:users,mobile,' . $user->id,
            'country' => 'required|in:' . $countries,
        ]);
        $user->mobile = $dialCode . $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->address = [
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => @$country,
        ];
        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;

        if (!$request->kv) {
            $user->kv = 0;
        } else {
            $user->kv = 1;
        }
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAccount($id)
    {
        $user = User::findOrFail($id);
        if ($user->delete_status == Status::NO) {
            $user->delete_status = Status::YES;
            $notify[] = ['success', 'User account deleted successfully'];
        } else {
            $user->delete_status = Status::NO;
            $notify[] = ['success', 'User account recovered successfully'];
        }
        $user->save();
        return back()->withNotify($notify);
    }

    public function status(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255'
            ]);
            $user->status = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[] = ['success', 'User banned successfully'];
        } else {
            $user->status = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[] = ['success', 'User unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);
    }


    public function showNotificationSingleForm($id)
    {
        $user = User::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.users.detail', $user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }



    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $users = User::active()->count();
        $pageTitle = 'Notification to Verified Users';
        return view('admin.users.notification_all', compact('pageTitle', 'users'));
    }

    public function sendNotificationAll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start'   => 'required',
            'batch'   => 'required',
        ]);
        if ($validator->fails()) return response()->json(['error' => $validator->errors()->all()]);
        $users = User::oldest()->active()->skip($request->start)->limit($request->batch)->get();
        foreach ($users as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ]);
        }

        return response()->json([
            'total_sent' => $users->count(),
        ]);
    }

    public function notificationLog($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $user->username;
        $logs = NotificationLog::where('user_id', $id)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user'));
    }

    public function showPromotionalNotificationForm()
    {
        $pageTitle = 'Promotional Notification to Users';
        return view('admin.users.promotional_notification_all', compact('pageTitle'));
    }

    public function sendPromotionalNotificationAll(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'message' => 'required',
            "image"  => ['nullable', new FileTypeValidate(['png', 'jpg', 'jpeg'])]
        ]);

        $users = User::active()->get();
        $image = null;

        if (File::isDirectory(getFilePath('promotional_notify'))) {
            $files = File::files(getFilePath('promotional_notify'));
            if (count($files) > 100) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }

        if ($request->hasFile('image')) {
            $image = getImage(getFilePath('promotional_notify') . '/' . fileUploader($request->image, getFilePath('promotional_notify'), getFileSize('promotional_notify')));
        }

        foreach ($users as $user) {
            notify($user, 'PROMOTIONAL_NOTIFY', [
                'title' => $request->title,
                'message' => $request->message
            ], ['push_notification'], image:$image);
        }

        $notify[] = ['success', 'Promotional notification send successfully'];
        return back()->withNotify($notify);
    }

    public function leaderBoard()
    {
        $pageTitle = 'Leader Board';
        $allTimeRank = User::select(['id' ,'username', 'firstname', 'lastname', 'score', 'avatar'])->selectRaw('(SELECT COUNT(DISTINCT score) FROM users u2 WHERE u2.score >= users.score) AS user_rank')->searchable(['username'])->orderBy('user_rank', 'asc')->paginate(getPaginate());

        return view('admin.users.leader_board', compact('pageTitle', 'allTimeRank'));
    }
}
