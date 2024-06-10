<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Constants\Status;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SocialiteController extends Controller
{
    public function socialLogin(Request $request)
    {
        $provider = $request->provider;
        if($provider != 'email' && $provider != 'mobile'){
            $notify[] = 'Provider must be email or mobile';
            return response()->json([
                'remark'  => 'provider_error',
                'status'  => 'error',
                'message' => ['error' => $notify]
            ]);
        }
        $emailRequired = 'nullable';
        $mobileRequired = 'nullable';
        if($provider == 'email'){
            $emailRequired = 'required';
        }
        if($provider == 'mobile'){
            $mobileRequired = 'required';
        }
        
        $validator = Validator::make($request->all(), [
            'provider' => 'required',
            'id' => 'required',
            'email' => [$emailRequired, 'email'],
            'mobile' => [$mobileRequired, 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }
        
        $userData = User::where('username', $request->id)->first();
        
        if (!$userData) {
            if(@$request->email){
                $emailExists = User::where('email', $request->email)->first();
                if ($emailExists) {
                    $notify[] = 'Email already exists';
                    return response()->json([
                        'remark'  => 'already_exists',
                        'status'  => 'error',
                        'message' => ['error' => $notify]
                    ]);
                }
            }
            if(@$request->mobile){
                $mobileExists = User::where('mobile', $request->mobile)->first();
                if ($mobileExists) {
                    $notify[] = 'Mobile already exists';
                    return response()->json([
                        'remark'  => 'already_exists',
                        'status'  => 'error',
                        'message' => ['error' => $notify]
                    ]);
                }
            }
            if(!$request->email && !$request->mobile){
                $notify[] = 'Credentials required';
                return response()->json([
                    'remark'  => 'credentials_error',
                    'status'  => 'error',
                    'message' => ['error' => $notify],
                ]);
            }
            $userData = $this->createUser($request);
        }
        
        $user = User::where('username', $request->id)->where($provider, $request->$provider)->first();
        if(!$user){
            $notify[] = ucfirst($provider) . ' not valid';
            return response()->json([
                'remark'  => 'not_found_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $tokenResult = $userData->createToken('auth_token')->plainTextToken;
        $this->loginLog($userData);
        $user = User::find($userData->id);
        $response[] = 'Login Successful';
        return response()->json([
            'remark'  => 'login_success',
            'status'  => 'success',
            'message' => ['success' => $response],
            'data'    => [
                'user'         => $user,
                'access_token' => $tokenResult,
                'token_type'   => 'Bearer',
            ],
        ]);
    }

    private function createUser($request) {
        $general  = gs();
        $password = getTrx(8);

        $firstName = preg_replace('/\W\w+\s*(\W*)$/', '$1', $request->name);

        $pieces   = explode(' ', $request->name);
        $lastName = array_pop($pieces);

        if ($pieces) {
            $firstName = $pieces[0];
        }
        if ($pieces) {
            $lastName = $lastName;
        }

        $newUser           = new User();
        $newUser->username = $request->id;

        if(@$request->email){
            $newUser->email = $request->email;
        }
        if(@$request->mobile){
            $newUser->mobile = $request->mobile;
        }

        $newUser->password  = Hash::make($password);
        $newUser->firstname = $firstName;
        $newUser->lastname  = $lastName;

        $newUser->address = [
            'address' => '',
            'state'   => '',
            'zip'     => '',
            'country' => '',
            'city'    => '',
        ];

        $newUser->status   = Status::VERIFIED;
        $newUser->ev       = 1;
        $newUser->sv       = 1;
        $newUser->login_by = $request->provider;
        $newUser->coins        = $general->welcome_bonus;

        $newUser->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $newUser->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $newUser->id);
        $adminNotification->save();

        return $newUser;
    }

    private function loginLog($user) {
        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();
    }
}
