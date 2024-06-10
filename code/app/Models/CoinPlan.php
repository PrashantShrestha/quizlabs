<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class CoinPlan extends Model
{
    use GlobalStatus, Searchable;
}
