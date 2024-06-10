<?php

namespace App\Http\Controllers\Admin;
use App\Models\Type;
use App\Http\Controllers\Controller;
use App\Traits\Crud;

class QuizTypeController extends Controller
{

    protected $title = 'Quiz Type';
    protected $model = Type::class;
    protected $view = 'admin.quiz.';
    protected $searchable = [];
    protected $operationFor = 'Quiz';
    protected $id;
    protected $relation = null;

    use Crud;

    public function __construct()
    {
        $this->id = request()->id;
    }
}
