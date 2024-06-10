<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\Searchable;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class QuizInfo extends Model
{
    use Searchable, GlobalStatus;
    
    protected $casts = [
        'exam_rule' => 'array'
    ];

    protected function examStartTime(): Attribute
    {
        return Attribute::make(
            get: fn (string $value = null) => Carbon::parse($value)->format('h:i a'),
        );
    }

    protected function examEndTime(): Attribute
    {
        return Attribute::make(
            get: fn (string $value = null) => Carbon::parse($value)->format('h:i a'),
        );
    }

    protected function winningMark(): Attribute
    {
        return Attribute::make(
            get: fn (string $value = null) => $value * 100,
        );
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }

    public function playInfo()
    {
        return $this->belongsTo(PlayInfo::class, 'id', 'quiz_info_id');
    }

    public function playQuestion()
    {
        return $this->hasManyThrough(PlayQuestion::class, PlayInfo::class);
    }

    public function scopeTypeFilter($query, $act)
    {
        return $query->where(['type_id' => function($q)use($act){
            $q->from('types')->where('act', $act)->select('id');
        }]);
    }

    public function scopeQuizInfoLists($query, $act, $order = 'asc')
    {
        $query->whereHas('type', function ($query) use($act) {
            $query->where('act', $act);
        })->orderBy('level_id', $order)->active();
    }

    public function scopeTypeCheck($query, $act)
    {
        $query->whereHas('type', function ($q) use($act) {
            $q->where('act', $act);
        });
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
