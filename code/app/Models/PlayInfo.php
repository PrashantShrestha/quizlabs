<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayInfo extends Model
{
    public function quizInfo()
    {
        return $this->belongsTo(QuizInfo::class);
    }

    public function playQuestions()
    {
        return $this->hasMany(PlayQuestion::class);
    }
}
