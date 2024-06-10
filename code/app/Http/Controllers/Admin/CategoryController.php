<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Traits\Crud;

class CategoryController extends Controller
{
    protected $title = 'Categories';
    protected $model = Category::class;
    protected $view = 'admin.category.';
    protected $searchable = ['name'];
    protected $operationFor = 'Category';
    protected $relation = null;

    use Crud;
}
