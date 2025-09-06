<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_values',
        'new_values',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create an audit log entry for a model action
     */
    public static function createLog(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            static::create([
                'user_id' => auth()->id(),
                'action' => strtoupper($action),
                'table_name' => $model->getTable(),
                'record_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'performed_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // If there's a foreign key constraint violation (e.g., when user is being deleted),
            // create the audit log without the user_id
            if (str_contains($e->getMessage(), 'audit_logs_user_id_foreign')) {
                static::create([
                    'user_id' => null,
                    'action' => strtoupper($action),
                    'table_name' => $model->getTable(),
                    'record_id' => $model->getKey(),
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'performed_at' => now(),
                ]);
            } else {
                // Re-throw other exceptions
                throw $e;
            }
        }
    }
}
