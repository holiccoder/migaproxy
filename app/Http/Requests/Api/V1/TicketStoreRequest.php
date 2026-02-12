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
        ];
    }
}
