<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
        $providers = array_keys((array) config('payments.gateways', []));

        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id'),
            ],
            'provider' => ['sometimes', 'string', Rule::in($providers)],
            'coupon_code' => ['sometimes', 'string', 'max:64'],
            'affiliate_code' => ['sometimes', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan_id.required' => 'Plan is required.',
            'plan_id.integer' => 'Plan must be a valid id.',
            'plan_id.exists' => 'Selected plan is not available for checkout.',
            'provider.in' => 'Selected payment provider is not supported.',
            'coupon_code.string' => 'Coupon code must be a string.',
            'coupon_code.max' => 'Coupon code may not be greater than 64 characters.',
            'affiliate_code.string' => 'Affiliate code must be a string.',
            'affiliate_code.max' => 'Affiliate code may not be greater than 64 characters.',
        ];
    }
}
