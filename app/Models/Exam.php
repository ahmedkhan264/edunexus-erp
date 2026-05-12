<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'class_id',
        'section',
        'subject_id',
        'teacher_id',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_marks',
        'passing_marks',
        'exam_type',
        'status',
        'instructions',
        'allow_retake',
        'max_attempts'
    ];

    protected $casts = [
        'exam_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'allow_retake' => 'boolean',
    ];

    // Relationships
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    // Scopes
    public function scopeForClass($query, $classId, $section = null)
    {
        $query->where('class_id', $classId);
        if ($section) {
            $query->where('section', $section);
        }
        return $query;
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByType($query, $examType)
    {
        return $query->where('exam_type', $examType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>', now())
                    ->where('status', 'scheduled')
                    ->orderBy('exam_date');
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
                    ->orderBy('exam_date', 'desc');
    }

    // Helper Methods
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isUpcoming(): bool
    {
        return $this->exam_date > now() && $this->status === 'scheduled';
    }

    public function hasStarted(): bool
    {
        return now() >= $this->start_time;
    }

    public function hasEnded(): bool
    {
        return now() >= $this->end_time;
    }

    public function canStart(): bool
    {
        return now() >= $this->start_time && 
               now() <= $this->end_time && 
               $this->status === 'scheduled';
    }

    public function getTimeRemaining(): string
    {
        if ($this->hasEnded()) {
            return 'Ended';
        }

        if ($this->hasStarted()) {
            $remaining = $this->end_time->diffForHumans(now(), true);
            return "Ends in {$remaining}";
        }

        $remaining = $this->start_time->diffForHumans(now(), true);
        return "Starts in {$remaining}";
    }

    public function getFormattedExamDate(): string
    {
        return $this->exam_date->format('M j, Y');
    }

    public function getFormattedStartTime(): string
    {
        return $this->start_time->format('g:i A');
    }

    public function getFormattedEndTime(): string
    {
        return $this->end_time->format('g:i A');
    }

    public function getFullDateTime(): string
    {
        return $this->exam_date->format('M j, Y') . ' ' . 
               $this->start_time->format('g:i A') . ' - ' . 
               $this->end_time->format('g:i A');
    }

    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'primary',
            'ongoing' => 'success',
            'completed' => 'info',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getExamTypeDisplay(): string
    {
        return match($this->exam_type) {
            'midterm' => 'Midterm Exam',
            'final' => 'Final Exam',
            'quiz' => 'Quiz',
            'assignment' => 'Assignment',
            'practical' => 'Practical Exam',
            default => 'Exam'
        };
    }

    public function getExamTypeColor(): string
    {
        return match($this->exam_type) {
            'midterm' => 'warning',
            'final' => 'danger',
            'quiz' => 'info',
            'assignment' => 'primary',
            'practical' => 'success',
            default => 'secondary'
        };
    }

    public function getTotalResults(): int
    {
        return $this->examResults()->count();
    }

    public function getPassedResults(): int
    {
        return $this->examResults()->where('status', 'pass')->count();
    }

    public function getFailedResults(): int
    {
        return $this->examResults()->where('status', 'fail')->count();
    }

    public function getAbsentResults(): int
    {
        return $this->examResults()->where('status', 'absent')->count();
    }

    public function getAverageScore(): float
    {
        return $this->examResults()
                   ->whereNotNull('marks_obtained')
                   ->avg('marks_obtained') ?? 0;
    }

    public function getHighestScore(): int
    {
        return $this->examResults()
                   ->whereNotNull('marks_obtained')
                   ->max('marks_obtained') ?? 0;
    }

    public function getLowestScore(): int
    {
        return $this->examResults()
                   ->whereNotNull('marks_obtained')
                   ->min('marks_obtained') ?? 0;
    }

    public function getPassRate(): float
    {
        $total = $this->getTotalResults();
        if ($total === 0) {
            return 0;
        }
        
        return ($this->getPassedResults() / $total) * 100;
    }

    public function getAttendanceRate(): float
    {
        $totalStudents = $this->schoolClass->students()->count();
        if ($totalStudents === 0) {
            return 0;
        }
        
        $attended = $this->getTotalResults() - $this->getAbsentResults();
        return ($attended / $totalStudents) * 100;
    }

    public function canStudentTakeExam($studentId): bool
    {
        if (!$this->canStart()) {
            return false;
        }

        $attempts = $this->examResults()
                       ->where('student_id', $studentId)
                       ->count();

        return $attempts < $this->max_attempts;
    }

    public function getStudentAttempts($studentId): int
    {
        return $this->examResults()
                   ->where('student_id', $studentId)
                   ->count();
    }

    public function getStudentBestResult($studentId): ?ExamResult
    {
        return $this->examResults()
                   ->where('student_id', $studentId)
                   ->orderBy('marks_obtained', 'desc')
                   ->first();
    }

    public function getGradeDistribution(): array
    {
        $distribution = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D+' => 0, 'D' => 0, 'D-' => 0,
            'F' => 0, 'Absent' => 0
        ];

        foreach ($this->examResults as $result) {
            if ($result->status === 'absent') {
                $distribution['Absent']++;
            } else {
                $distribution[$result->grade]++;
            }
        }

        return $distribution;
    }
}
