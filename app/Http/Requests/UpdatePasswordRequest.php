<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                Rule::exists('password_reset_tokens', 'token'),
            ],
            'email' => [
                'required',
                'email',
                Rule::exists('password_reset_tokens', 'email')
                    ->where('token', $this->input('token')),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ];
    }
}
