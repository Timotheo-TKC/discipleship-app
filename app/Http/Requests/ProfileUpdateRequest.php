<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ];

        // Role validation - only admins can change roles to admin/pastor
        if ($user->isAdmin()) {
            $rules['role'] = ['required', 'string', 'in:admin,pastor,member'];
        } else {
            // Non-admins cannot change their role - make it completely optional
            // The role field will be ignored in the controller for non-admin users
        }

        return $rules;
    }
}
