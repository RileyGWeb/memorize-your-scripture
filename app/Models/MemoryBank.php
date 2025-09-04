<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoryBank extends Model
{
    use HasFactory;
    
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

    protected $casts = [
        'verses' => 'array',
        'memorized_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
