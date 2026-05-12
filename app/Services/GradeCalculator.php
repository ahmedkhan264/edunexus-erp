<?php

namespace App\Services;

class GradeCalculator
{
    /**
     * Calculate grade based on percentage
     */
    public static function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'A+';
        } elseif ($percentage >= 85) {
            return 'A';
        } elseif ($percentage >= 80) {
            return 'A-';
        } elseif ($percentage >= 75) {
            return 'B+';
        } elseif ($percentage >= 70) {
            return 'B';
        } elseif ($percentage >= 65) {
            return 'B-';
        } elseif ($percentage >= 60) {
            return 'C+';
        } elseif ($percentage >= 55) {
            return 'C';
        } elseif ($percentage >= 50) {
            return 'C-';
        } elseif ($percentage >= 45) {
            return 'D+';
        } elseif ($percentage >= 40) {
            return 'D';
        } elseif ($percentage >= 35) {
            return 'D-';
        } else {
            return 'F';
        }
    }

    /**
     * Get grade points for GPA calculation
     */
    public static function getGradePoints(string $grade): float
    {
        $gradeScale = [
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
            'F' => 0.0
        ];

        return $gradeScale[$grade] ?? 0.0;
    }

    /**
     * Get color for grade display
     */
    public static function getGradeColor(string $grade): string
    {
        return match($grade) {
            'A+', 'A', 'A-' => 'success',
            'B+', 'B', 'B-' => 'info',
            'C+', 'C', 'C-' => 'warning',
            'D+', 'D', 'D-' => 'danger',
            'F' => 'dark',
            default => 'secondary'
        };
    }

    /**
     * Get performance level based on percentage
     */
    public static function getPerformanceLevel(float $percentage): string
    {
        if ($percentage >= 80) {
            return 'Excellent';
        } elseif ($percentage >= 70) {
            return 'Good';
        } elseif ($percentage >= 60) {
            return 'Average';
        } elseif ($percentage >= 50) {
            return 'Below Average';
        } elseif ($percentage >= 40) {
            return 'Poor';
        } else {
            return 'Very Poor';
        }
    }

    /**
     * Get performance color based on percentage
     */
    public static function getPerformanceColor(float $percentage): string
    {
        if ($percentage >= 80) {
            return 'success';
        } elseif ($percentage >= 70) {
            return 'info';
        } elseif ($percentage >= 60) {
            return 'primary';
        } elseif ($percentage >= 50) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Calculate GPA from grades
     */
    public static function calculateGPA(array $grades): float
    {
        if (empty($grades)) {
            return 0.0;
        }

        $totalPoints = 0;
        foreach ($grades as $grade) {
            $totalPoints += self::getGradePoints($grade);
        }

        return round($totalPoints / count($grades), 2);
    }

    /**
     * Determine if percentage is passing
     */
    public static function isPassing(float $percentage, float $passingPercentage = 40.0): bool
    {
        return $percentage >= $passingPercentage;
    }

    /**
     * Get grade remarks
     */
    public static function getGradeRemarks(string $grade): string
    {
        return match($grade) {
            'A+' => 'Outstanding Performance',
            'A' => 'Excellent Performance',
            'A-' => 'Very Good Performance',
            'B+' => 'Good Performance',
            'B' => 'Satisfactory Performance',
            'B-' => 'Above Average Performance',
            'C+' => 'Average Performance',
            'C' => 'Fair Performance',
            'C-' => 'Below Average Performance',
            'D+' => 'Poor Performance',
            'D' => 'Very Poor Performance',
            'D-' => 'Weak Performance',
            'F' => 'Fail',
            default => 'Not Graded'
        };
    }

    /**
     * Format percentage for display
     */
    public static function formatPercentage(float $percentage): string
    {
        return number_format($percentage, 1) . '%';
    }

    /**
     * Get grade distribution statistics
     */
    public static function getGradeDistribution(array $grades): array
    {
        $distribution = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D+' => 0, 'D' => 0, 'D-' => 0,
            'F' => 0
        ];

        foreach ($grades as $grade) {
            if (isset($distribution[$grade])) {
                $distribution[$grade]++;
            }
        }

        return $distribution;
    }

    /**
     * Calculate class ranking based on percentage
     */
    public static function calculateClassRank(float $percentage, array $classPercentages): int
    {
        $higherScores = array_filter($classPercentages, function($score) use ($percentage) {
            return $score > $percentage;
        });

        return count($higherScores) + 1;
    }

    /**
     * Get percentile rank
     */
    public static function getPercentileRank(float $percentage, array $classPercentages): float
    {
        if (empty($classPercentages)) {
            return 0.0;
        }

        $lowerScores = array_filter($classPercentages, function($score) use ($percentage) {
            return $score < $percentage;
        });

        return (count($lowerScores) / count($classPercentages)) * 100;
    }

    /**
     * Validate grade format
     */
    public static function isValidGrade(string $grade): bool
    {
        $validGrades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F'];
        return in_array($grade, $validGrades);
    }

    /**
     * Normalize grade (remove extra spaces, uppercase)
     */
    public static function normalizeGrade(string $grade): string
    {
        return strtoupper(trim($grade));
    }

    /**
     * Compare two grades and return the difference
     */
    public static function compareGrades(string $grade1, string $grade2): int
    {
        $gradeOrder = [
            'A+' => 1, 'A' => 2, 'A-' => 3,
            'B+' => 4, 'B' => 5, 'B-' => 6,
            'C+' => 7, 'C' => 8, 'C-' => 9,
            'D+' => 10, 'D' => 11, 'D-' => 12,
            'F' => 13
        ];

        $order1 = $gradeOrder[$grade1] ?? 14;
        $order2 = $gradeOrder[$grade2] ?? 14;

        return $order1 - $order2;
    }
}
