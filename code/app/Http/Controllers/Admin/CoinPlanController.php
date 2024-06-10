<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinPlan;
use App\Traits\Crud;

class CoinPlanController extends Controller
{
    protected $title = 'Coins Plan';
    protected $model = CoinPlan::class;
    protected $view = 'admin.coin_plan.';
    protected $searchable = ['title'];
    protected $operationFor = 'Plan';
    protected $id;
    protected $relation = null;

    use Crud;

    public function __construct()
    {
        $this->id = request()->id;
    }
}
