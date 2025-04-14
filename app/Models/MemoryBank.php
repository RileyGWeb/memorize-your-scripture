<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemoryBank extends Model
{
    protected $table = 'memory_bank';

    protected $fillable = [
        'user_id',
        'book',
        'chapter',
        'verses',
        'difficulty',
        'accuracy_score',
        'memorized_at',
        'user_text',
        'bible_translation',
    ];
}
