<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('identifier'))) {
            $this->merge([
                'identifier' => trim($this->input('identifier')),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'identifier' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'login_identifier'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Username/email wajib diisi.',
            'identifier.max' => 'Username/email maksimal 255 karakter.',
            'identifier.unique' => 'Username/email tersebut sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Ulangi password belum sesuai.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'identifier' => 'username/email',
            'password' => 'password',
        ];
    }
}
