<?php

namespace App\Policies;

use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LiveClassPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Students can view live classes for their own class/section
        if ($user->hasRole(['student'])) {
            return true;
        }
        
        // Teachers can view live classes they created
        if ($user->hasRole(['teacher'])) {
            return true;
        }
        
        // Admin and Super Admin can view all live classes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LiveClass $liveClass): bool
    {
        // Students can only view live classes for their own class/section
        if ($user->hasRole(['student'])) {
            $student = $user->student;
            if (!$student) {
                return false;
            }
            
            return $liveClass->class_id === $student->class_id && 
                   $liveClass->section === $student->section;
        }
        
        // Teachers can view live classes they created
        if ($user->hasRole(['teacher'])) {
            return $liveClass->teacher_id === $user->id;
        }
        
        // Admin and Super Admin can view all live classes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only teachers, admin, and super admin can create live classes
        return $user->hasRole(['teacher', 'admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LiveClass $liveClass): bool
    {
        // Teachers can only update their own live classes
        if ($user->hasRole(['teacher'])) {
            return $liveClass->teacher_id === $user->id;
        }
        
        // Admin and Super Admin can update any live class
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LiveClass $liveClass): bool
    {
        // Teachers can only delete their own live classes
        if ($user->hasRole(['teacher'])) {
            return $liveClass->teacher_id === $user->id;
        }
        
        // Admin and Super Admin can delete any live class
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LiveClass $liveClass): bool
    {
        // Teachers can only restore their own live classes
        if ($user->hasRole(['teacher'])) {
            return $liveClass->teacher_id === $user->id;
        }
        
        // Admin and Super Admin can restore any live class
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LiveClass $liveClass): bool
    {
        // Teachers can only permanently delete their own live classes
        if ($user->hasRole(['teacher'])) {
            return $liveClass->teacher_id === $user->id;
        }
        
        // Admin and Super Admin can permanently delete any live class
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can join the live class.
     */
    public function join(User $user, LiveClass $liveClass): bool
    {
        // Only students can join live classes
        if (!$user->hasRole(['student'])) {
            return false;
        }
        
        $student = $user->student;
        if (!$student) {
            return false;
        }
        
        // Student must be in the same class and section
        return $liveClass->class_id === $student->class_id && 
               $liveClass->section === $student->section;
    }
}
