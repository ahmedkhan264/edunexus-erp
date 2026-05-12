<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only principal and admin can create tasks
        $user = Auth::user();
        return $user && in_array($user->role_id, [1, 2]); // Super admin and principal
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title may not be greater than 255 characters.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'assigned_to.required' => 'Please select an assignee.',
            'assigned_to.exists' => 'Selected assignee does not exist.',
            'due_date.required' => 'Due date is required.',
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
            'priority.required' => 'Priority is required.',
            'priority.in' => 'Invalid priority selected.',
            'remarks.max' => 'Remarks may not be greater than 500 characters.',
        ];
    }
}
