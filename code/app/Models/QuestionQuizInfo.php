<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionQuizInfo extends Model
{
    protected $table = 'question_quiz_info';

    public function quizInfos()
    {
        return $this->belongsTo(QuizInfo::class, 'quiz_info_id', 'id');
    }
}
