<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetProxyCitiesRequest extends FormRequest
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
            'country_code' => ['required', 'string', 'size:2'],
            'state' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get the custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country_code.required' => 'Country code is required.',
            'country_code.string' => 'Country code must be a string.',
            'country_code.size' => 'Country code must be exactly 2 characters.',
            'state.required' => 'State is required.',
            'state.string' => 'State must be a string.',
            'state.max' => 'State may not be greater than 255 characters.',
        ];
    }
}
