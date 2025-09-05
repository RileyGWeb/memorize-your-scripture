<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditLog::createLog('CREATE', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            AuditLog::createLog('UPDATE', $model, $model->getOriginal(), $model->getAttributes());
        });

        static::deleting(function (Model $model) {
            // Use 'deleting' instead of 'deleted' to capture the audit log before the model is actually deleted
            AuditLog::createLog('DELETE', $model, $model->getAttributes(), null);
        });
    }
}
