<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\LiveClass;

class StoreLiveClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole(['teacher', 'admin', 'super_admin']);
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
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1|max:480',
            'meeting_link' => [
                'required',
                'url',
                'regex:/^(https?:\/\/)?(www\.)?(zoom\.us|meet\.google\.com|teams\.microsoft\.com)/'
            ],
            'notify_students' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Live class title is required.',
            'title.max' => 'Title may not be greater than 255 characters.',
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class is invalid.',
            'section.required' => 'Section selection is required.',
            'subject_id.required' => 'Subject selection is required.',
            'subject_id.exists' => 'Selected subject is invalid.',
            'date.required' => 'Date is required.',
            'date.after_or_equal' => 'Date cannot be in the past.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'duration.required' => 'Duration is required.',
            'duration.min' => 'Duration must be at least 1 minute.',
            'duration.max' => 'Duration may not be greater than 480 minutes (8 hours).',
            'meeting_link.required' => 'Meeting link is required.',
            'meeting_link.url' => 'Meeting link must be a valid URL.',
            'meeting_link.regex' => 'Meeting link must be from Zoom, Google Meet, or Microsoft Teams.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->fails()) {
                return;
            }

            // Check for time clashes
            $this->checkTimeClash($validator);
        });
    }

    /**
     * Check for time clashes with existing classes.
     */
    private function checkTimeClash($validator): void
    {
        $startDateTime = \Carbon\Carbon::parse($this->date . ' ' . $this->start_time);
        $endDateTime = $startDateTime->copy()->addMinutes($this->duration);

        // Create temporary live class instance for clash detection
        $tempLiveClass = new LiveClass([
            'class_id' => $this->class_id,
            'section' => $this->section,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime
        ]);

        $clashingClasses = $tempLiveClass->findClashingClasses();
        
        if ($clashingClasses->count() > 0) {
            $clashDetails = $clashingClasses->map(function ($class) {
                return $class->title . ' (' . $class->subject->name . ' - ' . 
                       $class->start_time->format('g:i A') . ' to ' . 
                       $class->end_time->format('g:i A') . ')';
            })->implode(', ');

            $validator->errors()->add('time_clash', 
                'Time clash detected with existing classes: ' . $clashDetails
            );
        }
    }
}
