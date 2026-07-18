<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),

            'action' => $action,

            'subject_type' => $subject
                ? $subject::class
                : null,

            'subject_id' => $subject?->getKey(),

            'description' => $description,

            'old_values' => $oldValues,

            'new_values' => $newValues,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),
        ]);
    }
}