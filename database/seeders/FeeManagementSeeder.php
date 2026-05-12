<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeeStructure;
use App\Models\FeeChallan;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('DELETE FROM fee_payments');
        DB::statement('DELETE FROM fee_challans');
        DB::statement('DELETE FROM fee_structures');

        $currentAcademicYear = '2024-2025';
        $classes = SchoolClass::all();
        $students = Student::all();
        $adminUser = User::where('email', 'admin@edunexus.com')->first();

        // Create fee structures for each class
        foreach ($classes as $class) {
            $this->createFeeStructuresForClass($class, $currentAcademicYear);
        }

        // Create fee challans for students
        foreach ($students as $student) {
            $this->createFeeChallansForStudent($student, $currentAcademicYear, $adminUser);
        }

        // Create sample fee payments
        $this->createSampleFeePayments($adminUser);

        $this->command->info('Fee Management data seeded successfully!');
    }

    private function createFeeStructuresForClass($class, $academicYear)
    {
        $feeStructures = [
            [
                'fee_type' => 'tuition',
                'amount' => $this->getTuitionFeeByGrade($class->grade_level),
                'payment_frequency' => 'monthly',
                'due_day' => 10,
                'late_fine_per_day' => 50,
                'is_optional' => false,
                'description' => 'Monthly tuition fee for ' . $class->name,
            ],
            [
                'fee_type' => 'admission',
                'amount' => 5000,
                'payment_frequency' => 'one_time',
                'due_day' => 15,
                'late_fine_per_day' => 0,
                'is_optional' => false,
                'description' => 'One-time admission fee for ' . $class->name,
            ],
            [
                'fee_type' => 'exam',
                'amount' => 2000,
                'payment_frequency' => 'quarterly',
                'due_day' => 5,
                'late_fine_per_day' => 100,
                'is_optional' => false,
                'description' => 'Quarterly examination fee for ' . $class->name,
            ],
            [
                'fee_type' => 'library',
                'amount' => 500,
                'payment_frequency' => 'monthly',
                'due_day' => 15,
                'late_fine_per_day' => 20,
                'is_optional' => true,
                'description' => 'Monthly library fee for ' . $class->name,
            ],
            [
                'fee_type' => 'sports',
                'amount' => 300,
                'payment_frequency' => 'monthly',
                'due_day' => 20,
                'late_fine_per_day' => 15,
                'is_optional' => true,
                'description' => 'Monthly sports fee for ' . $class->name,
            ],
            [
                'fee_type' => 'transport',
                'amount' => 1500,
                'payment_frequency' => 'monthly',
                'due_day' => 25,
                'late_fine_per_day' => 30,
                'is_optional' => true,
                'description' => 'Monthly transport fee for ' . $class->name,
            ],
        ];

        foreach ($feeStructures as $structure) {
            FeeStructure::create([
                'class_id' => $class->id,
                'academic_year' => $academicYear,
                'fee_type' => $structure['fee_type'],
                'amount' => $structure['amount'],
                'is_optional' => $structure['is_optional'],
                'payment_frequency' => $structure['payment_frequency'],
                'due_day' => $structure['due_day'],
                'late_fine_per_day' => $structure['late_fine_per_day'],
                'description' => $structure['description'],
                'is_active' => true,
            ]);
        }
    }

    private function getTuitionFeeByGrade($gradeLevel)
    {
        $fees = [
            1 => 2000, 2 => 2200, 3 => 2400, 4 => 2600, 5 => 2800, 6 => 3000,
            7 => 3500, 8 => 4000, 9 => 4500, 10 => 5000, 11 => 5500, 12 => 6000
        ];

        return $fees[$gradeLevel] ?? 3000;
    }

    private function createFeeChallansForStudent($student, $academicYear, $generatedBy)
    {
        // Create challans for current month and previous months
        $months = [1, 2, 3, 4, 5, 6]; // January to June

        foreach ($months as $month) {
            $feeStructures = FeeStructure::where('class_id', $student->class_id)
                ->where('academic_year', $academicYear)
                ->active()
                ->get();

            $totalAmount = 0;
            $dueDay = 10; // Default due day

            foreach ($feeStructures as $structure) {
                if ($structure->payment_frequency === 'monthly') {
                    $totalAmount += $structure->amount;
                    $dueDay = $structure->due_day;
                } elseif ($structure->payment_frequency === 'quarterly' && in_array($month, [1, 4, 7, 10])) {
                    $totalAmount += $structure->amount;
                    $dueDay = $structure->due_day;
                } elseif ($structure->payment_frequency === 'yearly' && $month === 1) {
                    $totalAmount += $structure->amount;
                    $dueDay = $structure->due_day;
                } elseif ($structure->payment_frequency === 'one_time' && $month === 1) {
                    $totalAmount += $structure->amount;
                    $dueDay = $structure->due_day;
                }
            }

            if ($totalAmount > 0) {
                $dueDate = Carbon::create(2024, $month, $dueDay);
                $status = $this->getRandomStatus($dueDate);

                $challan = FeeChallan::create([
                    'student_id' => $student->id,
                    'challan_number' => $this->generateChallanNumber($student->id, $month),
                    'academic_year' => $academicYear,
                    'month' => $month,
                    'total_amount' => $totalAmount,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'late_fine_applied' => $status === 'overdue' ? rand(100, 500) : 0,
                    'remarks' => $this->getRandomRemarks($status),
                    'generated_by' => $generatedBy?->id,
                ]);

                // Create payments for paid or partially paid challans
                if ($status === 'paid' || $status === 'partially_paid') {
                    $this->createPaymentForChallan($challan, $status, $generatedBy);
                }
            }
        }
    }

    private function getRandomStatus($dueDate)
    {
        $statuses = ['pending', 'paid', 'partially_paid', 'overdue'];
        
        // If due date is in the past, higher chance of being paid or overdue
        if ($dueDate < now()) {
            $weights = [10 => 'pending', 40 => 'paid', 20 => 'partially_paid', 30 => 'overdue'];
        } else {
            $weights = [60 => 'pending', 30 => 'paid', 10 => 'partially_paid', 0 => 'overdue'];
        }

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $weight => $status) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return 'pending';
    }

    private function getRandomRemarks($status)
    {
        $remarks = [
            'pending' => ['Payment pending', 'Awaiting payment', 'Due soon'],
            'paid' => ['Paid on time', 'Payment received', 'Transaction completed'],
            'partially_paid' => ['Partial payment made', 'Installment paid', 'Balance remaining'],
            'overdue' => ['Payment overdue', 'Late payment', 'Follow up required'],
        ];

        $statusRemarks = $remarks[$status] ?? [''];
        return $statusRemarks[array_rand($statusRemarks)];
    }

    private function generateChallanNumber($studentId, $month)
    {
        static $challanCounter = 1;
        $year = date('Y');
        $monthPadded = str_pad($month, 2, '0', STR_PAD_LEFT);
        $sequence = str_pad($challanCounter++, 4, '0', STR_PAD_LEFT);
        
        return "CH-{$year}-{$monthPadded}-{$sequence}";
    }

    private function createPaymentForChallan($challan, $status, $receivedBy)
    {
        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'online'];
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

        if ($status === 'paid') {
            $amountPaid = $challan->total_amount + $challan->late_fine_applied;
        } else {
            // Partial payment - pay 50-80% of total
            $amountPaid = $challan->total_amount * (rand(50, 80) / 100);
        }

        FeePayment::create([
            'challan_id' => $challan->id,
            'student_id' => $challan->student_id,
            'payment_date' => $this->getRandomPaymentDate($challan->due_date),
            'amount_paid' => $amountPaid,
            'payment_method' => $paymentMethod,
            'transaction_id' => $paymentMethod === 'online' ? 'TXN' . rand(100000, 999999) : null,
            'receipt_number' => $this->generateReceiptNumber(),
            'received_by' => $receivedBy?->id,
            'remarks' => 'Payment for ' . $challan->month_name . ' fee',
        ]);
    }

    private function getRandomPaymentDate($dueDate)
    {
        $minDate = $dueDate->copy()->subDays(5);
        $maxDate = $dueDate->copy()->addDays(30);
        
        return Carbon::createFromTimestamp(
            rand($minDate->timestamp, $maxDate->timestamp)
        );
    }

    private function generateReceiptNumber()
    {
        static $counter = 1;
        $date = now()->format('Ymd');
        $sequence = str_pad($counter++, 4, '0', STR_PAD_LEFT);
        
        return "RCPT-{$date}-{$sequence}";
    }

    private function createSampleFeePayments($receivedBy)
    {
        // Create some additional sample payments for testing
        $students = Student::take(10)->get();
        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'online'];

        foreach ($students as $student) {
            $challan = FeeChallan::where('student_id', $student->id)
                ->where('status', 'pending')
                ->first();

            if ($challan && rand(1, 3) === 1) { // 33% chance of additional payment
                FeePayment::create([
                    'challan_id' => $challan->id,
                    'student_id' => $student->id,
                    'payment_date' => now()->subDays(rand(1, 30)),
                    'amount_paid' => $challan->total_amount * 0.3, // 30% partial payment
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_id' => 'TXN' . rand(100000, 999999),
                    'receipt_number' => $this->generateReceiptNumber(),
                    'received_by' => $receivedBy?->id,
                    'remarks' => 'Partial payment received',
                ]);

                // Update challan status to partially paid
                $challan->status = 'partially_paid';
                $challan->save();
            }
        }
    }
}
