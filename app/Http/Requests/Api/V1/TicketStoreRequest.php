<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TicketStoreRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'category' => ['required', 'string', 'in:billing,technical,feature_request'],
            'context' => ['sometimes', 'array'],
            'context.browser' => ['sometimes', 'string', 'max:255'],
            'context.os' => ['sometimes', 'string', 'max:255'],
            'context.plan_type' => ['sometimes', 'string', 'max:255'],
            'attachment' => ['sometimes', 'file', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'Subject is required.',
            'subject.max' => 'Subject may not be greater than 255 characters.',
            'message.required' => 'Message is required.',
            'message.max' => 'Message may not be greater than 10000 characters.',
            'priority.in' => 'Priority must be low, medium, or high.',
            'category.required' => 'Category is required.',
            'category.in' => 'Category must be billing, technical, or feature request.',
            'attachment.max' => 'Attachment may not be greater than 5 MB.',
        ];
    }
}
