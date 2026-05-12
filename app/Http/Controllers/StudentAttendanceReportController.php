<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceReportRequest;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentAttendanceReportController extends Controller
{
    protected $attendanceReportService;

    public function __construct(AttendanceReportService $attendanceReportService)
    {
        $this->attendanceReportService = $attendanceReportService;
    }

    /**
     * Show the student attendance report page.
     */
    public function index(AttendanceReportRequest $request): View
    {
        $validated = $request->validated();
        
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $reportData = $this->attendanceReportService->generateReport($validated);

        return view('reports.student-attendance', compact('classes', 'reportData', 'validated'));
    }

    /**
     * Export attendance report to PDF.
     */
    public function exportPdf(AttendanceReportRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $reportData = $this->attendanceReportService->generateReport($validated);

        $pdf = \PDF::loadView('reports.student-attendance-pdf', compact('reportData', 'validated'))
            ->setPaper('a4', 'landscape')
            ->setOption('margin-bottom', 15);

        $filename = 'student-attendance-report-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Export attendance report to Excel.
     */
    public function exportExcel(AttendanceReportRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $reportData = $this->attendanceReportService->generateReport($validated);

        $filename = 'student-attendance-report-' . now()->format('Y-m-d') . '.xlsx';

        return new StreamedResponse(function () use ($reportData, $validated) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Student Attendance Report');
            $sheet->setCellValue('A2', 'Date Range: ' . $validated['from_date'] . ' to ' . $validated['to_date']);
            if (!empty($validated['class_id'])) {
                $class = SchoolClass::find($validated['class_id']);
                $sheet->setCellValue('A3', 'Class: ' . $class->name);
            }
            $sheet->setCellValue('A4', 'Generated on: ' . now()->format('Y-m-d H:i:s'));

            // Column headers
            $sheet->setCellValue('A6', 'Roll No');
            $sheet->setCellValue('B6', 'Student Name');
            $sheet->setCellValue('C6', 'Class');
            $sheet->setCellValue('D6', 'Total Days');
            $sheet->setCellValue('E6', 'Present');
            $sheet->setCellValue('F6', 'Absent');
            $sheet->setCellValue('G6', 'Late');
            $sheet->setCellValue('H6', 'Attendance %');

            // Data rows
            $row = 7;
            foreach ($reportData['students'] as $student) {
                $sheet->setCellValue('A' . $row, $student['roll_number'] ?? '-');
                $sheet->setCellValue('B' . $row, $student['name']);
                $sheet->setCellValue('C' . $row, $student['class_name']);
                $sheet->setCellValue('D' . $row, $student['total_days']);
                $sheet->setCellValue('E' . $row, $student['present_days']);
                $sheet->setCellValue('F' . $row, $student['absent_days']);
                $sheet->setCellValue('G' . $row, $student['late_days']);
                $sheet->setCellValue('H' . $row, number_format($student['attendance_percentage'], 2) . '%');
                $row++;
            }

            // Summary
            $summaryRow = $row + 2;
            $sheet->setCellValue('A' . $summaryRow, 'Summary:');
            $sheet->setCellValue('B' . $summaryRow, 'Total Students: ' . count($reportData['students']));
            $sheet->setCellValue('C' . $summaryRow, 'Avg Attendance: ' . number_format($reportData['summary']['average_attendance'], 2) . '%');

            // Auto-size columns
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Get chart data for AJAX requests.
     */
    public function getChartData(AttendanceReportRequest $request)
    {
        $validated = $request->validated();
        $chartData = $this->attendanceReportService->generateChartData($validated);

        return response()->json([
            'success' => true,
            'chartData' => $chartData
        ]);
    }
}
