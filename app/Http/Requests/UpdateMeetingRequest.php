<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        $meeting = $this->route('meeting');
        
        if (!$user || !$meeting) {
            return false;
        }

        // Principal can update all meetings
        if ($user->role_id === 2) { // Principal
            return true;
        }

        // Meeting creator can update their own meetings
        if ($meeting->created_by === $user->id) {
            return true;
        }

        return false;
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
            'agenda' => 'nullable|string|max:2000',
            'meeting_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
            'send_reminder_24h' => 'sometimes|boolean',
            'send_reminder_1h' => 'sometimes|boolean',
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
            'title.required' => 'Meeting title is required.',
            'title.max' => 'Meeting title may not be greater than 255 characters.',
            'description.max' => 'Description may not be greater than 1000 characters.',
            'agenda.max' => 'Agenda may not be greater than 2000 characters.',
            'meeting_date.required' => 'Meeting date is required.',
            'meeting_date.date' => 'Please provide a valid date.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'location.required' => 'Location is required.',
            'location.max' => 'Location may not be greater than 255 characters.',
            'participants.required' => 'Please select at least one participant.',
            'participants.array' => 'Participants must be an array.',
            'participants.min' => 'Please select at least one participant.',
            'participants.*.exists' => 'Selected participant does not exist.',
        ];
    }
}
