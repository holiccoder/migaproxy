<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ChangeProxyPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ipmart_id' => ['required', 'string', 'max:255'],
            'proxyPwd' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ipmart_id.required' => 'IPmart id is required.',
            'ipmart_id.string' => 'IPmart id must be a string.',
            'ipmart_id.max' => 'IPmart id may not be greater than 255 characters.',
            'proxyPwd.required' => 'Proxy password is required.',
            'proxyPwd.string' => 'Proxy password must be a string.',
            'proxyPwd.min' => 'Proxy password must be at least 6 characters.',
            'proxyPwd.max' => 'Proxy password may not be greater than 255 characters.',
        ];
    }
}
