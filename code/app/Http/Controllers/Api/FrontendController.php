<?php

namespace App\Http\Controllers\Api;

use App\Models\Frontend;
use App\Http\Controllers\Controller;

class FrontendController extends Controller
{
    public function policyPages() {
        $notify[]    = 'Policy Page';
        $policyPages = Frontend::where('data_keys', 'policy_pages.element')->get();

        return response()->json([
            'remark'  => 'policy',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data'    => [
                'policy_pages' => $policyPages,
            ],
        ]);
    }
}
