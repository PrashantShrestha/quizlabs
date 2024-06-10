<?php

namespace App\Http\Controllers\Api;

use App\Models\CoinPlan;
use App\Http\Controllers\Controller;
use App\Models\CoinLog;

class CoinPlanController extends Controller
{
    public function coinStore()
    {
        $coinPlans = CoinPlan::active()->orderBy('id', 'desc')->get();
        $notify[] = '';
        return response()->json([
            'remark'  => 'coin_store',
            'status'  => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'coinPlans' => $coinPlans
            ]
        ]);
    }

    public function coinHistory()
    {
        $coinLogs = CoinLog::where('user_id', auth()->id())->with('coinPlan')->orderby('id', 'desc')->get();

        $notify[] = '';
        return response()->json([
            'remark' => 'coin_log',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'coinLogs' => $coinLogs
            ]
        ]);
    }
}
