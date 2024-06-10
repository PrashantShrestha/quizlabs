<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use GlobalStatus, Searchable;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'id');
    }

    public function quizInfos()
    {
        return $this->belongsToMany(QuizInfo::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function playQuestions()
    {
        return $this->hasMany(PlayQuestion::class);
    }
}
