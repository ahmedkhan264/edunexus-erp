<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - {{ $salarySlipData['employee']['name'] }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #007bff;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        
        .salary-slip-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .employee-info {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .info-table .label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 25%;
        }
        
        .salary-details {
            margin-bottom: 20px;
        }
        
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .salary-table th,
        .salary-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: right;
        }
        
        .salary-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .salary-table .description {
            text-align: left;
        }
        
        .salary-table .total {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .salary-table .net-salary {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .attendance-summary {
            margin-bottom: 20px;
        }
        
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        
        .attendance-table th {
            background-color: #6c757d;
            color: white;
            font-weight: bold;
        }
        
        .deduction-breakdown {
            margin-bottom: 20px;
        }
        
        .deduction-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .deduction-table th,
        .deduction-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: right;
        }
        
        .deduction-table th {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .deduction-table .description {
            text-align: left;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 30px;
        }
        
        .signature-label {
            font-size: 11px;
            color: #666;
        }
        
        .amount-in-words {
            font-weight: bold;
            font-style: italic;
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: #ccc;
            opacity: 0.1;
            z-index: -1;
            font-weight: bold;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .company-info h2 {
            font-size: 20px;
            margin: 0;
            color: #333;
        }
        
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="watermark">EDUNEXUS</div>
    
    <div class="company-info">
        <h2>EduNexus ERP + LMS</h2>
        <p>123 Education Street, Knowledge City, Pakistan</p>
        <p>Phone: +92-300-1234567 | Email: info@edunexus.com</p>
    </div>
    
    <div class="header">
        <h1>Salary Slip</h1>
        <p>For the month of {{ $salarySlipData['payroll']['month'] }}</p>
    </div>
    
    <div class="salary-slip-title">
        Salary Statement
    </div>
    
    <div class="employee-info">
        <table class="info-table">
            <tr>
                <td class="label">Employee Name</td>
                <td>{{ $salarySlipData['employee']['name'] }}</td>
                <td class="label">Employee Code</td>
                <td>{{ $salarySlipData['employee']['email'] }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td>
                <td>{{ $salarySlipData['employee']['role'] }}</td>
                <td class="label">Joining Date</td>
                <td>{{ $salarySlipData['employee']['joining_date'] }}</td>
            </tr>
            <tr>
                <td class="label">Contact</td>
                <td>{{ $salarySlipData['employee']['phone'] }}</td>
                <td class="label">Email</td>
                <td>{{ $salarySlipData['employee']['email'] }}</td>
            </tr>
        </table>
    </div>
    
    <div class="salary-details">
        <table class="salary-table">
            <thead>
                <tr>
                    <th colspan="4">Earnings</th>
                </tr>
                <tr>
                    <th class="description">Description</th>
                    <th>Amount (Rs.)</th>
                    <th class="description">Description</th>
                    <th>Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="description">Basic Salary</td>
                    <td>{{ $salarySlipData['payroll']['basic_salary'] }}</td>
                    <td class="description">Total Earnings</td>
                    <td>{{ $salarySlipData['payroll']['gross_salary'] }}</td>
                </tr>
                <tr>
                    <td class="description">Allowances</td>
                    <td>{{ $salarySlipData['payroll']['allowances'] }}</td>
                    <td class="description" colspan="2"></td>
                </tr>
                <tr class="total">
                    <td class="description">Gross Salary</td>
                    <td>{{ $salarySlipData['payroll']['gross_salary'] }}</td>
                    <td class="description">Total Earnings</td>
                    <td>{{ $salarySlipData['payroll']['gross_salary'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="deduction-breakdown">
        <table class="deduction-table">
            <thead>
                <tr>
                    <th colspan="4">Deductions</th>
                </tr>
                <tr>
                    <th class="description">Description</th>
                    <th>Days</th>
                    <th>Daily Rate (Rs.)</th>
                    <th>Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="description">Absent Days</td>
                    <td>{{ $salarySlipData['deduction_breakdown']['absent_days'] }}</td>
                    <td>{{ number_format($salarySlipData['deduction_breakdown']['daily_salary'], 2) }}</td>
                    <td>{{ number_format($salarySlipData['deduction_breakdown']['absent_deductions'], 2) }}</td>
                </tr>
                <tr>
                    <td class="description">Late Days (3 late = 1 day)</td>
                    <td>{{ $salarySlipData['deduction_breakdown']['late_deduction_days'] ?? 0 }}</td>
                    <td>{{ number_format($salarySlipData['deduction_breakdown']['daily_salary'], 2) }}</td>
                    <td>{{ number_format($salarySlipData['deduction_breakdown']['late_deductions'], 2) }}</td>
                </tr>
                <tr class="total">
                    <td class="description">Total Deductions</td>
                    <td colspan="2"></td>
                    <td>{{ number_format($salarySlipData['deduction_breakdown']['total_deductions'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="attendance-summary">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Working Days</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                    <th>Late Days</th>
                    <th>Leave Days</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $salarySlipData['attendance']['working_days'] }}</td>
                    <td>{{ $salarySlipData['attendance']['present_days'] }}</td>
                    <td>{{ $salarySlipData['attendance']['absent_days'] }}</td>
                    <td>{{ $salarySlipData['attendance']['late_days'] }}</td>
                    <td>{{ $salarySlipData['attendance']['leave_days'] }}</td>
                    <td>{{ number_format($salarySlipData['attendance']['percentage'], 1) }}%</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="amount-in-words">
        <strong>Net Payable Amount:</strong> {{ $salarySlipData['payroll']['net_salary_words'] }}
    </div>
    
    <div class="salary-details">
        <table class="salary-table">
            <tbody>
                <tr class="net-salary">
                    <td class="description" colspan="3">NET PAYABLE AMOUNT</td>
                    <td>{{ $salarySlipData['payroll']['net_salary'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Employee Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Authorized Signatory</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #666;">
            <p>This is a computer-generated salary slip and does not require signature.</p>
            <p>Generated on: {{ date('d M Y H:i') }} | Generated by: {{ $salarySlipData['processed_by'] }}</p>
            @if($salarySlipData['finalized_by'])
                <p>Finalized by: {{ $salarySlipData['finalized_by'] }} on {{ $salarySlipData['finalized_at'] }}</p>
            @endif
        </div>
    </div>
</body>
</html>
