<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use Searchable, GlobalStatus;

    protected $fillable = [
        'level'
    ];

    public function quizInfos()
    {
        return $this->hasMany(QuizInfo::class);
    }
}
