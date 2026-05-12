<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): View
    {
        // Employee roles (excluding students and parents)
        $employeeRoles = [2, 3, 4, 5, 6, 7, 8, 9]; // Principal, Admin, Teacher, Student, Parent, Accountant, HR Manager, Librarian, Timetable Coordinator
        
        $query = User::whereIn('role_id', $employeeRoles);
        
        // Apply filters
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role_id', $request->role);
        }
        
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('department') && $request->department !== 'all') {
            $query->where('department', $request->department);
        }
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $employees = $query->orderBy('name', 'asc')
                         ->paginate(15);
        
        // Get role names for dropdown
        $roles = [
            2 => 'Principal',
            3 => 'Teacher',
            4 => 'Admin',
            5 => 'Student',
            6 => 'Parent',
            7 => 'Accountant',
            8 => 'HR Manager',
            9 => 'Librarian',
        ];
        
        // Get departments for dropdown (mock data - would need to be dynamic)
        $departments = [
            'Teaching' => 'Teaching',
            'Administration' => 'Administration',
            'Accounts' => 'Accounts',
            'HR' => 'HR',
            'Library' => 'Library',
        ];
        
        return view('hr.employees.index', compact('employees', 'roles', 'departments'));
    }
    
    /**
     * Toggle employee status (active/inactive).
     */
    public function toggleStatus(Request $request, User $employee): JsonResponse
    {
        // Check if user can manage employees
        if (!auth()->user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage employees'
            ], 403);
        }
        
        // Prevent deactivating self
        if ($employee->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account'
            ], 400);
        }
        
        $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
        $employee->update(['status' => $newStatus]);
        
        return response()->json([
            'success' => true,
            'message' => "Employee status updated to {$newStatus}",
            'new_status' => $newStatus
        ]);
    }
    
    /**
     * Show the employee profile.
     */
    public function show(User $employee): View
    {
        // Check if user can view employee details
        if (!auth()->user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            abort(403, 'You are not authorized to view employee details');
        }
        
        $employee->load(['role', 'attendance' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(30);
        }]);
        
        return view('hr.employees.show', compact('employee'));
    }
    
    /**
     * Show the form for editing the specified employee.
     */
    public function edit(User $employee): View
    {
        // Check if user can edit employees
        if (!auth()->user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            abort(403, 'You are not authorized to edit employees');
        }
        
        $roles = [
            2 => 'Principal',
            3 => 'Teacher',
            4 => 'Admin',
            7 => 'Accountant',
            8 => 'HR Manager',
            9 => 'Librarian',
        ];
        
        $departments = [
            'Teaching' => 'Teaching',
            'Administration' => 'Administration',
            'Accounts' => 'Accounts',
            'HR' => 'HR',
            'Library' => 'Library',
        ];
        
        return view('hr.employees.edit', compact('employee', 'roles', 'departments'));
    }
    
    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, User $employee): JsonResponse
    {
        // Check if user can update employees
        if (!auth()->user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update employees'
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);
        
        $employee->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'employee' => $employee
        ]);
    }
}
