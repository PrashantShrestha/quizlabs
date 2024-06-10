<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use GlobalStatus, Searchable;

    protected $fillable = [
        'name',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function questions()
    {
        return $this->hasManyThrough(QuestionQuizInfo::class, QuizInfo::class);
    }

    public function quizInfos()
    {
        return $this->hasMany(QuizInfo::class, 'sub_category_id');
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
        $query->withCount(['questions' => function ($question) use($act) {
            $question->whereHas('quizInfos', function ($quizInfo) use($act){
                $quizInfo->whereHas('type', function ($q) use($act) {
                    $q->where('act', $act);
                });
            });
        }]);
    }
}
