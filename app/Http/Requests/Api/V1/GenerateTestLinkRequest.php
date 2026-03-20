<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GenerateTestLinkRequest extends FormRequest
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
            'protocol' => ['nullable', 'integer', 'in:0,2'],
            'pattern' => ['nullable', 'integer', 'min:0'],
            'rule' => ['nullable', 'integer', 'min:0'],
            'count' => ['nullable', 'integer', 'min:0'],
            'country' => ['nullable', 'string', 'size:2'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'protocol.integer' => 'Protocol must be an integer.',
            'protocol.in' => 'Protocol must be 0 (HTTP) or 2 (SOCKS5).',
            'pattern.integer' => 'Pattern must be an integer.',
            'pattern.min' => 'Pattern must be zero or greater.',
            'rule.integer' => 'Rule must be an integer.',
            'rule.min' => 'Rule must be zero or greater.',
            'count.integer' => 'Count must be an integer.',
            'count.min' => 'Count must be zero or greater.',
            'country.string' => 'Country must be a string.',
            'country.size' => 'Country must be exactly 2 characters.',
            'state.string' => 'State must be a string.',
            'state.max' => 'State may not be greater than 255 characters.',
            'city.string' => 'City must be a string.',
            'city.max' => 'City may not be greater than 255 characters.',
        ];
    }
}
