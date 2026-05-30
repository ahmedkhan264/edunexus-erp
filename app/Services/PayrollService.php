<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Payroll;
use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PayrollService
{
    /**
     * Generate salary slip for a teacher for a given month/year.
     */
    public function generateSalarySlip(Teacher $teacher, $month, $year)
    {
        // Check if already generated
        $existing = SalarySlip::where('teacher_id', $teacher->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existing) {
            return $existing;
        }

        $payroll = Payroll::where('teacher_id', $teacher->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payroll) {
            // Auto-create payroll if missing
            $payroll = $this->calculatePayroll($teacher, $month, $year);
        }

        $data = [
            'teacher' => $teacher,
            'payroll' => $payroll,
            'month' => $month,
            'year' => $year,
            'school_name' => config('app.name', 'EduNexus'),
        ];

        $pdf = Pdf::loadView('hr.payroll.salary-slip-pdf', $data);
        $fileName = "salary_slip_{$teacher->employee_code}_{$year}_{$month}.pdf";
        $path = "salary_slips/{$fileName}";
        Storage::disk('public')->put($path, $pdf->output());

        $salarySlip = SalarySlip::create([
            'teacher_id' => $teacher->id,
            'payroll_id' => $payroll->id,
            'month' => $month,
            'year' => $year,
            'file_path' => $path,
            'generated_at' => now(),
        ]);

        return $salarySlip;
    }

    /**
     * Calculate payroll for a teacher (basic salary + deductions + allowances).
     */
    public function calculatePayroll(Teacher $teacher, $month, $year)
    {
        $basicSalary = $teacher->basic_salary;
        $allowances = $basicSalary * 0.20; // 20% allowances
        $deductions = $basicSalary * 0.10;  // 10% deductions
        $netSalary = $basicSalary + $allowances - $deductions;

        $payroll = Payroll::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'basic_salary' => $basicSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'status' => 'pending',
            ]
        );

        return $payroll;
    }

    /**
     * Process payroll for all teachers for a given month/year.
     */
    public function processMonthlyPayroll($month, $year)
    {
        $teachers = Teacher::all();
        $results = [];

        foreach ($teachers as $teacher) {
            $payroll = $this->calculatePayroll($teacher, $month, $year);
            $slip = $this->generateSalarySlip($teacher, $month, $year);
            $results[] = [
                'teacher' => $teacher->user->name,
                'net_salary' => $payroll->net_salary,
                'slip_path' => $slip->file_path,
            ];
        }

        return $results;
    }
}
