<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'mentor_id' => [
                'required',
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', ['admin', 'pastor', 'mentor']);
                }),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'schedule_type' => ['required', 'in:weekly,biweekly,monthly,custom'],
            'schedule_day' => ['required_if:schedule_type,weekly,biweekly', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'schedule_time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'class_outline' => ['nullable', 'string', 'max:5000'],
            'weekly_topics' => ['nullable', 'array'],
            'weekly_topics.*' => ['nullable', 'string', 'max:255'],
            'weekly_content' => ['nullable', 'array'],
            'weekly_content.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'mentor_id.exists' => 'The selected mentor must be an admin, pastor, or mentor.',
            'schedule_day.required_if' => 'Schedule day is required for weekly and biweekly schedules.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after' => 'End date must be after the start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'mentor_id' => 'mentor',
            'duration_weeks' => 'duration (weeks)',
            'schedule_type' => 'schedule type',
            'schedule_day' => 'schedule day',
            'schedule_time' => 'schedule time',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
