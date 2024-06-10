<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Image;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'General Setting';
        $timezones = json_decode(file_get_contents(resource_path('views/admin/partials/timezone.json')));
        return view('admin.setting.general', compact('pageTitle', 'timezones'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:40',
            'cur_text' => 'required|string|max:40',
            'cur_sym' => 'required|string|max:40',
            'base_color' => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            'secondary_color' => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            'timezone' => 'required',
        ]);

        $general = gs();
        $general->site_name = $request->site_name;
        $general->cur_text = $request->cur_text;
        $general->cur_sym = $request->cur_sym;
        $general->base_color = str_replace('#', '', $request->base_color);
        $general->secondary_color = str_replace('#', '', $request->secondary_color);
        $general->save();

        $timezoneFile = config_path('timezone.php');
        $content = '<?php $timezone = ' . $request->timezone . ' ?>';
        file_put_contents($timezoneFile, $content);
        $notify[] = ['success', 'General setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function systemConfiguration()
    {
        $pageTitle = 'System Configuration';
        return view('admin.setting.configuration', compact('pageTitle'));
    }


    public function systemConfigurationSubmit(Request $request)
    {
        $general = gs();
        $general->kv = $request->kv ? Status::ENABLE : Status::DISABLE;
        $general->ev = $request->ev ? Status::ENABLE : Status::DISABLE;
        $general->en = $request->en ? Status::ENABLE : Status::DISABLE;
        $general->sv = $request->sv ? Status::ENABLE : Status::DISABLE;
        $general->sn = $request->sn ? Status::ENABLE : Status::DISABLE;
        $general->push_notification = $request->push_notification ? Status::ENABLE : Status::DISABLE;
        $general->force_ssl = $request->force_ssl ? Status::ENABLE : Status::DISABLE;
        $general->secure_password = $request->secure_password ? Status::ENABLE : Status::DISABLE;
        $general->registration = $request->registration ? Status::ENABLE : Status::DISABLE;
        $general->agree = $request->agree ? Status::ENABLE : Status::DISABLE;
        $general->multi_language = $request->multi_language ? Status::ENABLE : Status::DISABLE;
        $general->google_login = $request->google_login ? Status::ENABLE : Status::DISABLE;
        $general->mobile_login = $request->mobile_login ? Status::ENABLE : Status::DISABLE;
        $general->save();
        $notify[] = ['success', 'System configuration updated successfully'];
        return back()->withNotify($notify);
    }


    public function logoIcon()
    {
        $pageTitle = 'Logo & Favicon';
        return view('admin.setting.logo_icon', compact('pageTitle'));
    }

    public function logoIconUpdate(Request $request)
    {
        $request->validate([
            'logo' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'favicon' => ['image', new FileTypeValidate(['png'])],
        ]);
        if ($request->hasFile('logo')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->logo)->save($path . '/logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }

        if ($request->hasFile('favicon')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $size = explode('x', getFileSize('favicon'));
                Image::make($request->favicon)->resize($size[0], $size[1])->save($path . '/favicon.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the favicon'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'Logo & favicon updated successfully'];
        return back()->withNotify($notify);
    }

    public function maintenanceMode()
    {
        $pageTitle = 'Maintenance Mode';
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        return view('admin.setting.maintenance', compact('pageTitle', 'maintenance'));
    }

    public function maintenanceModeSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        $general = GeneralSetting::first();
        $general->maintenance_mode = $request->status ? Status::ENABLE : Status::DISABLE;
        $general->save();

        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        $maintenance->data_values = [
            'description' => $request->description,
        ];
        $maintenance->save();

        $notify[] = ['success', 'Maintenance mode updated successfully'];
        return back()->withNotify($notify);
    }

    public function quizSetting()
    {
        $pageTitle = 'Quiz Setting';
        return view('admin.setting.quiz', compact('pageTitle'));
    }

    public function quizStore(Request $request)
    {
        $request->validate([
            'gq_ans_duration'           => 'required|integer',
            'contest_ans_duration'      => 'required|integer',
            'guess_ans_duration'        => 'required|integer',
            'daily_quiz_ans_duration'   => 'required|integer',
            'gq_score'                  => 'required|integer',
            'contest_score'             => 'required|integer',
            'guess_score'               => 'required|integer',
            'daily_quiz_score'          => 'required|integer',
            'fun_score'                 => 'required|integer',
            'exam_score'                => 'required|integer',
            'welcome_bonus'             => 'required|integer',
            'battle_participate_point'  => 'required|integer',
            'per_level_question'        => 'required|integer',
            'on_select_ans_status'      => 'required|integer',
            'per_battle_question'       => 'required|integer',
            'fun_ans_duration'          => 'required|integer',
        ]);

        $gs = gs();
        $gs->gq_ans_duration            = $request->gq_ans_duration;
        $gs->contest_ans_duration       = $request->contest_ans_duration;
        $gs->guess_ans_duration         = $request->guess_ans_duration;
        $gs->daily_quiz_ans_duration    = $request->daily_quiz_ans_duration;
        $gs->gq_score                   = $request->gq_score;
        $gs->contest_score              = $request->contest_score;
        $gs->guess_score                = $request->guess_score;
        $gs->daily_quiz_score           = $request->daily_quiz_score;
        $gs->fun_score                  = $request->fun_score;
        $gs->exam_score                 = $request->exam_score;
        $gs->welcome_bonus              = $request->welcome_bonus;
        $gs->battle_participate_point   = $request->battle_participate_point;
        $gs->per_level_question         = $request->per_level_question;
        $gs->on_select_ans_status       = $request->on_select_ans_status;
        $gs->per_battle_question        = $request->per_battle_question;
        $gs->fun_ans_duration           = $request->fun_ans_duration;
        $gs->save();

        $notify[] = ['success', 'Quiz settings updated successfully'];
        return back()->withNotify($notify);
    }

        public function ads()
    {
        $pageTitle = 'Ads Configuration';
        return view('admin.setting.ads', compact('pageTitle'));
    }

    public function adsStore(Request $request)
    {
        $general = gs();
        $general->banner_ads_id = $request->banner_ads_id;
        $general->interstitial_unit_id = $request->interstitial_unit_id;
        $general->rewarded_unit_id = $request->rewarded_unit_id;
        $general->ios_banner_ads_id = $request->ios_banner_ads_id;
        $general->ios_interstitial_unit_id = $request->ios_rewarded_unit_id;
        $general->ios_rewarded_unit_id = $request->ios_rewarded_unit_id;
        $general->banner_ads_status = $request->banner_ads_status ? 1 : 0;
        $general->interstitial_unit_status = $request->interstitial_unit_status ? 1 : 0;
        $general->rewarded_unit_status = $request->rewarded_unit_status ? 1 : 0;
        $general->save();

        $notify[] = ['success', 'Ads configuration update successfully'];
        return back()->withNotify($notify);
    }
}
