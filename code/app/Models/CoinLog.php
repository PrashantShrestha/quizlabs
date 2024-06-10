<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinLog extends Model
{
    public function coinPlan()
    {
        return $this->belongsTo(CoinPlan::class);
    }
}
