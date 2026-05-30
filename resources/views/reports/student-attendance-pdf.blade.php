<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 18px;
            color: #007bff;
        }
        .summary-item p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .progress-bar {
            width: 60px;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            margin-right: 5px;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        @media print {
            body { margin: 10px; }
            .header { page-break-after: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EduNexus ERP + LMS</h1>
        <h2>Student Attendance Report</h2>
        <p><strong>Date Range:</strong> {{ $reportData['date_range']['from'] }} to {{ $reportData['date_range']['to'] }}</p>
        <p><strong>Class:</strong> {{ $reportData['filters']['class'] }}</p>
        @if($reportData['filters']['section'] != 'All Sections')
            <p><strong>Section:</strong> {{ $reportData['filters']['section'] }}</p>
        @endif
        @if($reportData['filters']['student'] != 'All Students')
            <p><strong>Student:</strong> {{ $reportData['filters']['student'] }}</p>
        @endif
        <p><strong>Generated on:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <h3>{{ $reportData['summary']['total_students'] }}</h3>
                <p>Total Students</p>
            </div>
            <div class="summary-item">
                <h3>{{ $reportData['summary']['total_present'] }}</h3>
                <p>Present Days</p>
            </div>
            <div class="summary-item">
                <h3>{{ $reportData['summary']['total_absent'] }}</h3>
                <p>Absent Days</p>
            </div>
            <div class="summary-item">
                <h3>{{ $reportData['summary']['total_late'] }}</h3>
                <p>Late Days</p>
            </div>
            <div class="summary-item">
                <h3>{{ number_format($reportData['summary']['average_attendance'], 1) }}%</h3>
                <p>Avg Attendance</p>
            </div>
            <div class="summary-item">
                <h3>{{ $reportData['date_range']['days'] }}</h3>
                <p>Total Days</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="60">Roll No</th>
                <th>Student Name</th>
                <th width="80">Class</th>
                <th width="60">Total</th>
                <th width="60">Present</th>
                <th width="60">Absent</th>
                <th width="60">Late</th>
                <th width="100">Attendance %</th>
                <th width="80">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['students'] as $student)
            <tr>
                <td>{{ $student['roll_number'] ?? '-' }}</td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['class_name'] }}</td>
                <td>{{ $student['total_days'] }}</td>
                <td>
                    <span class="badge badge-success">{{ $student['present_days'] }}</span>
                </td>
                <td>
                    <span class="badge badge-danger">{{ $student['absent_days'] }}</span>
                </td>
                <td>
                    <span class="badge badge-warning">{{ $student['late_days'] }}</span>
                </td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $student['attendance_percentage'] }}%"></div>
                    </div>
                    {{ number_format($student['attendance_percentage'], 1) }}%
                </td>
                <td>
                    <span class="badge badge-{{ $student['status_color'] }}">
                        {{ $student['attendance_percentage'] >= 95 ? 'Excellent' : 
                           ($student['attendance_percentage'] >= 85 ? 'Good' : 
                           ($student['attendance_percentage'] >= 75 ? 'Average' : 'Poor')) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px;">
                    No attendance records found for the selected criteria
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($reportData['students']))
    <div class="summary">
        <h3>Summary Statistics</h3>
        <p><strong>Total Students:</strong> {{ count($reportData['students']) }}</p>
        <p><strong>Average Attendance:</strong> {{ number_format($reportData['summary']['average_attendance'], 2) }}%</p>
        <p><strong>Perfect Attendance (100%):</strong> {{ $reportData['summary']['perfect_attendance'] }} students</p>
        <p><strong>Low Attendance (&lt;75%):</strong> {{ $reportData['summary']['low_attendance'] }} students</p>
    </div>
    @endif

    <div class="footer">
        <p>Generated by EduNexus ERP + LMS - Student Attendance Report</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>