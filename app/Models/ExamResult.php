<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'marks_obtained',
        'total_marks',
        'percentage',
        'grade',
        'status',
        'remarks',
        'submitted_at',
        'graded_by',
        'graded_at',
        'attempt_number'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'percentage' => 'decimal:2',
    ];

    // Relationships
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Scopes
    public function scopeForExam($query, $examId)
    {
        return $query->where('exam_id', $examId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopePassed($query)
    {
        return $query->where('status', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'fail');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    public function scopeUngraded($query)
    {
        return $query->whereNull('graded_at');
    }

    // Helper Methods
    public function isPassed(): bool
    {
        return $this->status === 'pass';
    }

    public function isFailed(): bool
    {
        return $this->status === 'fail';
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    public function isGraded(): bool
    {
        return !is_null($this->graded_at);
    }

    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'pass' => 'Pass',
            'fail' => 'Fail',
            'absent' => 'Absent',
            default => 'Unknown'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pass' => 'success',
            'fail' => 'danger',
            'absent' => 'warning',
            default => 'secondary'
        };
    }

    public function getGradeColor(): string
    {
        return match($this->grade) {
            'A+' => 'success',
            'A' => 'success',
            'A-' => 'success',
            'B+' => 'info',
            'B' => 'info',
            'B-' => 'info',
            'C+' => 'warning',
            'C' => 'warning',
            'C-' => 'warning',
            'D+' => 'danger',
            'D' => 'danger',
            'D-' => 'danger',
            'F' => 'dark',
            default => 'secondary'
        };
    }

    public function getFormattedPercentage(): string
    {
        return number_format($this->percentage, 1) . '%';
    }

    public function getFormattedSubmittedDate(): string
    {
        return $this->submitted_at ? $this->submitted_at->format('M j, Y g:i A') : 'N/A';
    }

    public function getFormattedGradedDate(): string
    {
        return $this->graded_at ? $this->graded_at->format('M j, Y g:i A') : 'N/A';
    }

    public function getMarksDisplay(): string
    {
        return "{$this->marks_obtained} / {$this->total_marks}";
    }

    public function getGradePoints(): float
    {
        return match($this->grade) {
            'A+' => 4.0,
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'D-' => 0.7,
            'F' => 0.0,
            default => 0.0
        };
    }

    public function calculateGrade(): string
    {
        $percentage = $this->percentage;

        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 85 => 'A',
            $percentage >= 80 => 'A-',
            $percentage >= 75 => 'B+',
            $percentage >= 70 => 'B',
            $percentage >= 65 => 'B-',
            $percentage >= 60 => 'C+',
            $percentage >= 55 => 'C',
            $percentage >= 50 => 'C-',
            $percentage >= 45 => 'D+',
            $percentage >= 40 => 'D',
            $percentage >= 35 => 'D-',
            default => 'F'
        };
    }

    public function calculateStatus(): string
    {
        if ($this->status === 'absent') {
            return 'absent';
        }

        return $this->marks_obtained >= $this->exam->passing_marks ? 'pass' : 'fail';
    }

    public function calculatePercentage(): float
    {
        if ($this->total_marks === 0) {
            return 0;
        }

        return ($this->marks_obtained / $this->total_marks) * 100;
    }

    public function updateGradeAndStatus(): void
    {
        $this->percentage = $this->calculatePercentage();
        $this->grade = $this->calculateGrade();
        $this->status = $this->calculateStatus();
        $this->save();
    }

    public function getRemarksSummary(): string
    {
        return $this->remarks ? Str::limit($this->remarks, 50) : 'No remarks';
    }

    public function getPerformanceLevel(): string
    {
        if ($this->isAbsent()) {
            return 'Absent';
        }

        $percentage = $this->percentage;

        return match(true) {
            $percentage >= 80 => 'Excellent',
            $percentage >= 70 => 'Good',
            $percentage >= 60 => 'Average',
            $percentage >= 50 => 'Below Average',
            $percentage >= 40 => 'Poor',
            default => 'Very Poor'
        };
    }

    public function getPerformanceColor(): string
    {
        if ($this->isAbsent()) {
            return 'warning';
        }

        $percentage = $this->percentage;

        return match(true) {
            $percentage >= 80 => 'success',
            $percentage >= 70 => 'info',
            $percentage >= 60 => 'primary',
            $percentage >= 50 => 'warning',
            default => 'danger'
        };
    }

    public function isImprovement(?ExamResult $previousResult): bool
    {
        if (!$previousResult || $previousResult->isAbsent()) {
            return true;
        }

        return $this->percentage > $previousResult->percentage;
    }

    public function getImprovement(?ExamResult $previousResult): string
    {
        if (!$previousResult || $previousResult->isAbsent()) {
            return 'N/A';
        }

        $difference = $this->percentage - $previousResult->percentage;
        $sign = $difference >= 0 ? '+' : '';
        
        return "{$sign}" . number_format($difference, 1) . '%';
    }

    public function getImprovementColor(?ExamResult $previousResult): string
    {
        if (!$previousResult || $previousResult->isAbsent()) {
            return 'info';
        }

        return $this->isImprovement($previousResult) ? 'success' : 'danger';
    }

    public function getRank(): ?int
    {
        if ($this->isAbsent()) {
            return null;
        }

        $rank = $this->exam->examResults()
                          ->where('status', '!=', 'absent')
                          ->orderBy('percentage', 'desc')
                          ->pluck('id')
                          ->search($this->id);

        return $rank !== false ? $rank + 1 : null;
    }

    public function getTotalStudents(): int
    {
        return $this->exam->examResults()->count();
    }

    public function getPosition(): string
    {
        $rank = $this->getRank();
        $total = $this->getTotalStudents();

        if ($rank === null) {
            return 'Absent';
        }

        return "{$rank} / {$total}";
    }

    public function getPercentile(): ?float
    {
        $rank = $this->getRank();
        $total = $this->getTotalStudents();

        if ($rank === null) {
            return null;
        }

        return (($total - $rank) / $total) * 100;
    }

    public function getFormattedPercentile(): string
    {
        $percentile = $this->getPercentile();
        
        if ($percentile === null) {
            return 'N/A';
        }

        return number_format($percentile, 1) . 'th';
    }

    // Static methods for grade calculation
    public static function calculateGradeFromPercentage(float $percentage): string
    {
        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 85 => 'A',
            $percentage >= 80 => 'A-',
            $percentage >= 75 => 'B+',
            $percentage >= 70 => 'B',
            $percentage >= 65 => 'B-',
            $percentage >= 60 => 'C+',
            $percentage >= 55 => 'C',
            $percentage >= 50 => 'C-',
            $percentage >= 45 => 'D+',
            $percentage >= 40 => 'D',
            $percentage >= 35 => 'D-',
            default => 'F'
        };
    }

    public static function calculateStatusFromMarks(int $marksObtained, int $passingMarks): string
    {
        return $marksObtained >= $passingMarks ? 'pass' : 'fail';
    }

    public static function calculatePercentageFromMarks(int $marksObtained, int $totalMarks): float
    {
        if ($totalMarks === 0) {
            return 0;
        }

        return ($marksObtained / $totalMarks) * 100;
    }
}
