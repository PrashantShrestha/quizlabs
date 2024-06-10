<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'option'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
