<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HelpArticleIndexRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'category' => ['sometimes', 'string', 'max:255', 'exists:help_categories,slug'],
            'search' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page may not be greater than 50.',
            'category.string' => 'Category must be a string.',
            'category.max' => 'Category may not be greater than 255 characters.',
            'category.exists' => 'Category must be a valid slug.',
            'search.string' => 'Search must be a string.',
            'search.max' => 'Search may not be greater than 255 characters.',
        ];
    }
}
