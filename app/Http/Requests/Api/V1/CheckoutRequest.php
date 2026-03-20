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
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id'),
            ],
            'payment_method' => ['sometimes', 'string', Rule::in([
                'wallet',
                'credit_card',
                'paypal',
                'alipay',
                'wechat_pay',
            ])],
            'coupon_code' => ['sometimes', 'string', 'max:64'],
            'affiliate_code' => ['sometimes', 'string', 'max:64'],
            'order_comment' => ['sometimes', 'string', 'max:1000'],
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
            'payment_method.in' => 'Selected payment method is not supported.',
            'coupon_code.string' => 'Coupon code must be a string.',
            'coupon_code.max' => 'Coupon code may not be greater than 64 characters.',
            'affiliate_code.string' => 'Affiliate code must be a string.',
            'affiliate_code.max' => 'Affiliate code may not be greater than 64 characters.',
            'order_comment.string' => 'Order comment must be a string.',
            'order_comment.max' => 'Order comment may not be greater than 1000 characters.',
        ];
    }
}
