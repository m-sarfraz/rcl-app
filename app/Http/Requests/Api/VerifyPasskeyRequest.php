<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPasskeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'passkey' => ['required', 'string', 'min:6', 'max:64'],
            'label'   => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'passkey.required' => 'Enter the scoring passkey.',
        ];
    }
}
