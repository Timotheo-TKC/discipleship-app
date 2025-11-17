<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionRequest extends FormRequest
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
        $classId = $this->route('class')?->id;

        return [
            'class_id' => ['required', 'exists:classes,id'],
            'session_date' => ['required', 'date', 'after_or_equal:today'],
            'topic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'google_meet_link' => ['nullable', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:300'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_date.after_or_equal' => 'Session date cannot be in the past.',
            'duration_minutes.min' => 'Session duration must be at least 15 minutes.',
            'duration_minutes.max' => 'Session duration cannot exceed 5 hours.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'session_date' => 'session date',
            'duration_minutes' => 'duration (minutes)',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set the class_id from the route parameter
        if ($this->route('class')) {
            $this->merge([
                'class_id' => $this->route('class')->id,
            ]);
        }
    }
}
