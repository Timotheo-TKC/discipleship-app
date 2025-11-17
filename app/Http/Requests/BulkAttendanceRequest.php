<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->canManageClasses();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_session_id' => ['required', 'exists:class_sessions,id'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.member_id' => ['required', 'exists:members,id'],
            'attendance.*.status' => ['required', 'in:present,absent,excused'],
            'attendance.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'attendance.required' => 'At least one attendance record is required.',
            'attendance.*.member_id.required' => 'Member ID is required for each attendance record.',
            'attendance.*.member_id.exists' => 'Invalid member ID provided.',
            'attendance.*.status.required' => 'Status is required for each attendance record.',
            'attendance.*.status.in' => 'Status must be present, absent, or excused.',
        ];
    }
}

