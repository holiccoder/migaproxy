<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()?->getKey();

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'skype_profile' => ['nullable', 'string', 'max:255'],
            'telegram_profile' => ['nullable', 'string', 'max:255'],
            'facebook_profile' => ['nullable', 'string', 'max:255'],
            'x_profile' => ['nullable', 'string', 'max:255'],
            'youtube_profile' => ['nullable', 'string', 'max:255'],
            'instagram_profile' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'Email has already been taken.',
            'avatar.image' => 'Avatar must be an image file.',
            'avatar.max' => 'Avatar may not be greater than 2 MB.',
            'skype_profile.max' => 'Skype profile may not be greater than 255 characters.',
            'telegram_profile.max' => 'Telegram profile may not be greater than 255 characters.',
            'facebook_profile.max' => 'Facebook profile may not be greater than 255 characters.',
            'x_profile.max' => 'X profile may not be greater than 255 characters.',
            'youtube_profile.max' => 'YouTube profile may not be greater than 255 characters.',
            'instagram_profile.max' => 'Instagram profile may not be greater than 255 characters.',
        ];
    }
}
