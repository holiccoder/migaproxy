<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetProxyApiLinkRequest extends FormRequest
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
            'cntryCode' => ['required', 'string', 'size:2'],
            'time' => ['required', 'integer', 'min:1'],
            'num' => ['required', 'integer', 'min:1'],
            'format' => ['required', 'integer', 'in:1,2'],
            'apiCntryCode' => ['nullable', 'string', 'size:2'],
            'stateName' => ['nullable', 'string', 'max:255'],
            'cityName' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cntryCode.required' => 'Country code is required.',
            'cntryCode.string' => 'Country code must be a string.',
            'cntryCode.size' => 'Country code must be exactly 2 characters.',
            'time.required' => 'Time is required.',
            'time.integer' => 'Time must be an integer.',
            'time.min' => 'Time must be at least 1.',
            'num.required' => 'Number of IPs is required.',
            'num.integer' => 'Number of IPs must be an integer.',
            'num.min' => 'Number of IPs must be at least 1.',
            'format.required' => 'Format is required.',
            'format.integer' => 'Format must be an integer.',
            'format.in' => 'Format must be 1 or 2.',
            'apiCntryCode.string' => 'API country code must be a string.',
            'apiCntryCode.size' => 'API country code must be exactly 2 characters.',
            'stateName.string' => 'State name must be a string.',
            'stateName.max' => 'State name may not be greater than 255 characters.',
            'cityName.string' => 'City name must be a string.',
            'cityName.max' => 'City name may not be greater than 255 characters.',
        ];
    }
}
