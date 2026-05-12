<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'agenda',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'minutes',
        'created_by',
        'send_reminder_24h',
        'send_reminder_1h',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'meeting_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'send_reminder_24h' => 'boolean',
        'send_reminder_1h' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created the meeting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the participants of the meeting.
     */
    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    /**
     * Get the users participating in the meeting.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'meeting_participants')
                    ->withPivot('attendance_status', 'attended_at', 'remarks')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include upcoming meetings.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('meeting_date', '>=', Carbon::today())
                   ->orderBy('meeting_date', 'asc')
                   ->orderBy('start_time', 'asc');
    }

    /**
     * Scope a query to only include past meetings.
     */
    public function scopePast($query)
    {
        return $query->where('meeting_date', '<', Carbon::today())
                   ->orderBy('meeting_date', 'desc')
                   ->orderBy('start_time', 'desc');
    }

    /**
     * Scope a query to only include today's meetings.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('meeting_date', Carbon::today())
                   ->orderBy('start_time', 'asc');
    }

    /**
     * Scope a query to only include meetings created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Check if the meeting is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->meeting_date >= Carbon::today();
    }

    /**
     * Check if the meeting is today.
     */
    public function isToday(): bool
    {
        return $this->meeting_date->isToday();
    }

    /**
     * Check if the meeting is past.
     */
    public function isPast(): bool
    {
        return $this->meeting_date < Carbon::today();
    }

    /**
     * Check if the meeting is in progress.
     */
    public function isInProgress(): bool
    {
        if (!$this->isToday()) {
            return false;
        }

        $now = Carbon::now();
        $startTime = Carbon::createFromTime(
            $this->start_time->format('H'),
            $this->start_time->format('i'),
            0,
            $this->meeting_date
        );
        $endTime = Carbon::createFromTime(
            $this->end_time->format('H'),
            $this->end_time->format('i'),
            0,
            $this->meeting_date
        );

        return $now->between($startTime, $endTime);
    }

    /**
     * Get the formatted meeting date.
     */
    public function getFormattedDate(): string
    {
        return $this->meeting_date ? $this->meeting_date->format('M j, Y') : 'N/A';
    }

    /**
     * Get the formatted meeting time range.
     */
    public function getFormattedTimeRange(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return 'N/A';
        }

        return $this->start_time->format('h:i A') . ' - ' . $this->end_time->format('h:i A');
    }

    /**
     * Get the full formatted datetime.
     */
    public function getFullDateTime(): string
    {
        return $this->getFormattedDate() . ' at ' . $this->getFormattedTimeRange();
    }

    /**
     * Get the meeting duration in minutes.
     */
    public function getDuration(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::createFromTime(
            $this->start_time->format('H'),
            $this->start_time->format('i'),
            0
        );
        $end = Carbon::createFromTime(
            $this->end_time->format('H'),
            $this->end_time->format('i'),
            0
        );

        return $start->diffInMinutes($end);
    }

    /**
     * Get the meeting status.
     */
    public function getStatus(): string
    {
        if ($this->isInProgress()) {
            return 'In Progress';
        } elseif ($this->isPast()) {
            return 'Completed';
        } elseif ($this->isToday()) {
            return 'Today';
        } else {
            return 'Upcoming';
        }
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->getStatus()) {
            'In Progress' => 'info',
            'Completed' => 'success',
            'Today' => 'warning',
            'Upcoming' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Get the number of participants.
     */
    public function getParticipantCount(): int
    {
        return $this->participants()->count();
    }

    /**
     * Get the number of present participants.
     */
    public function getPresentCount(): int
    {
        return $this->participants()->where('attendance_status', 'present')->count();
    }

    /**
     * Get the number of absent participants.
     */
    public function getAbsentCount(): int
    {
        return $this->participants()->where('attendance_status', 'absent')->count();
    }

    /**
     * Get the attendance percentage.
     */
    public function getAttendancePercentage(): float
    {
        $total = $this->getParticipantCount();
        if ($total === 0) {
            return 0;
        }

        return ($this->getPresentCount() / $total) * 100;
    }

    /**
     * Check if the meeting requires reminders.
     */
    public function requiresReminders(): bool
    {
        return $this->send_reminder_24h || $this->send_reminder_1h;
    }

    /**
     * Check if the meeting is online.
     */
    public function isOnline(): bool
    {
        return str_contains(strtolower($this->location), 'http') || 
               str_contains(strtolower($this->location), 'zoom') || 
               str_contains(strtolower($this->location), 'teams') || 
               str_contains(strtolower($this->location), 'meet');
    }

    /**
     * Get the location type.
     */
    public function getLocationType(): string
    {
        return $this->isOnline() ? 'Online' : 'Physical';
    }

    /**
     * Add a participant to the meeting.
     */
    public function addParticipant(int $userId): MeetingParticipant
    {
        return $this->participants()->create([
            'user_id' => $userId,
            'attendance_status' => 'pending',
        ]);
    }

    /**
     * Remove a participant from the meeting.
     */
    public function removeParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->delete() > 0;
    }

    /**
     * Mark attendance for a participant.
     */
    public function markAttendance(int $userId, string $status, ?string $remarks = null): bool
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        
        if (!$participant) {
            return false;
        }

        $updateData = ['attendance_status' => $status];
        
        if ($status === 'present') {
            $updateData['attended_at'] = now();
        }
        
        if ($remarks) {
            $updateData['remarks'] = $remarks;
        }

        return $participant->update($updateData);
    }

    /**
     * Check if a user can manage this meeting.
     */
    public function canBeManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Principal and admin can manage all meetings
        if (in_array($user->role_id, [1, 2])) { // Super admin and principal
            return true;
        }

        // Meeting creator can manage their own meetings
        if ($this->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view this meeting.
     */
    public function canBeViewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // If user can manage, they can view
        if ($this->canBeManagedBy($user)) {
            return true;
        }

        // Check if user is a participant
        return $this->participants()->where('user_id', $user->id)->exists();
    }
}
