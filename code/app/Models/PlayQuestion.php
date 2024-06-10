<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayQuestion extends Model
{
    protected $table = 'play_questions';

    protected $casts = [
        'options_id' => 'array',
    ];

    public function playInfo()
    {
        return $this->belongsTo(PlayInfo::class);
    }
}
