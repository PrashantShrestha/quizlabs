<?php

namespace App\Http\Controllers\Admin;

use App\Models\SubCategory;
use App\Http\Controllers\Controller;
use App\Traits\Crud;

class SubCategoryController extends Controller
{

    protected $title = 'Subcategories';
    protected $model = SubCategory::class;
    protected $view = 'admin.subcategory.';
    protected $searchable = ['name', 'category:name'];
    protected $operationFor = 'Subcategory';
    protected $relation = 'category';

    use Crud;
}
