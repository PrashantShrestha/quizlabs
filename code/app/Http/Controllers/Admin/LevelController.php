<?php

namespace App\Http\Controllers\Admin;

use App\Models\Level;
use App\Http\Controllers\Controller;
use App\Traits\Crud;

class LevelController extends Controller
{

    protected $title = 'Levels';
    protected $model = Level::class;
    protected $view = 'admin.level.';
    protected $searchable = [];
    protected $operationFor = 'Level';
    protected $id;
    protected $relation = null;

    use Crud;

    public function __construct()
    {
        $this->hasImage = false;
        $this->id = request()->id;
    }
}
