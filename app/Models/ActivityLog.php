<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_id',
        'action',
        'description',
        'properties'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent loggable model.
     */
    public function loggable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include logs for a specific action.
     */
    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('created_at', [$fromDate, $toDate]);
    }

    /**
     * Create a new activity log entry.
     */
    public static function log($loggable, $userId, $action, $description, $properties = null)
    {
        return $loggable->activityLogs()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties
        ]);
    }
}
