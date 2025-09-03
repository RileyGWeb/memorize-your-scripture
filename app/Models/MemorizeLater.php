<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizeLater extends Model
{
    use HasFactory;

    protected $table = 'memorize_later';

    protected $fillable = [
        'user_id',
        'book',
        'chapter', 
        'verses',
        'note',
        'added_at',
    ];

    protected $casts = [
        'verses' => 'array',
        'added_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class MemorizeLater extends Model
{
    //
}
