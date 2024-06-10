<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;
use App\Http\Controllers\Controller;

class SiteController extends Controller
{
    public function changeLanguage($lang = null)
    {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        $languageData = json_decode(file_get_contents(resource_path('lang/' . $lang . '.json')));
        $languages    = Language::get();
        $notify[] = 'Change site language successfully';
        return response()->json([
            'remark'=>'change_language',
            'status'=>'success',
            'message'=>['success'=>$notify],
            'data'=>[
                'language_data' => $languageData,
                'languages'     => $languages,
            ],
        ]);
    }
}
