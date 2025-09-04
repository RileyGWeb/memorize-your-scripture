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

        static::deleted(function (Model $model) {
            AuditLog::createLog('DELETE', $model, $model->getAttributes(), null);
        });
    }
}
