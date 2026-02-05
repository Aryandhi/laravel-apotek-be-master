<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::log(
                action: 'created',
                model: $model,
                newValues: $model->getActivityLogAttributes()
            );
        });

        static::updated(function ($model) {
            $oldValues = collect($model->getOriginal())
                ->only(array_keys($model->getDirty()))
                ->toArray();

            $newValues = $model->getDirty();

            if (! empty($newValues)) {
                ActivityLog::log(
                    action: 'updated',
                    model: $model,
                    oldValues: $oldValues,
                    newValues: $newValues
                );
            }
        });

        static::deleted(function ($model) {
            ActivityLog::log(
                action: 'deleted',
                model: $model,
                oldValues: $model->getActivityLogAttributes()
            );
        });
    }

    public function getActivityLogAttributes(): array
    {
        // Override this method in model to customize logged attributes
        $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

        return collect($this->getAttributes())
            ->except($hidden)
            ->toArray();
    }
}
