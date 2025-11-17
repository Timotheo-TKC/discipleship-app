<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassContentRequest extends FormRequest
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
            'content' => ['nullable', 'string'],
            'content_type' => ['required', 'in:outline,lesson,assignment,resource,homework,reading,video,document'],
            'week_number' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'integer', 'min:0'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'url', 'max:500'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The content title is required.',
            'content_type.required' => 'Please select a content type.',
            'content_type.in' => 'The selected content type is invalid.',
            'week_number.min' => 'Week number must be at least 1.',
            'order.min' => 'Order must be at least 0.',
            'attachments.*.url' => 'Each attachment must be a valid URL.',
        ];
    }
}
