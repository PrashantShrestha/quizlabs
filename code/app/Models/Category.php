<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use GlobalStatus, Searchable;

    protected $fillable = [
        'name',
    ];

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id');
    }

    public function quizInfos()
    {
        return $this->hasMany(QuizInfo::class, 'category_id');
    }

    public function questions()
    {
        return $this->hasManyThrough(QuestionQuizInfo::class, QuizInfo::class);
    }

    public function scopeSubcategoryCount($query, $act)
    {
        $query->withCount(['subcategories' => function ($subcategory) use($act) {
            $subcategory->whereHas('quizInfos', function ($quizInfo) use($act) {
                $quizInfo->active()->whereHas('type', function ($q) use($act) {
                    $q->where('act', $act)->active();
                });
            })->active();
        }]);
    }

    public function scopeQuizInfoCount($query, $act)
    {
        $query->withCount(['quizInfos' => function ($quizInfo) use($act) {
            $quizInfo->active()->whereHas('type', function ($query) use($act) {
                $query->where('act', $act)->active();
            });
        }]);
    }

    public function scopeQuestionCount($query, $act)
    {
        $query->withCount(['questions' => function ($question) use ($act) {
            $question->whereHas('quizInfos', function ($quizInfo) use ($act) {
                $quizInfo->active()->whereHas('type', function ($q) use ($act) {
                    $q->where('act', $act)->active();
                });
            });
        }]);
    }
}
