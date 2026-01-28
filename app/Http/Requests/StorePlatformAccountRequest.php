<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformAccountRequest extends FormRequest
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
            'platform' => [
                'required',
                Rule::in(array_column(Platform::cases(), 'value')),
            ],
            'username' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'platform.required' => __('messages.field_required'),
            'platform.in' => __('messages.invalid_platform'),
            'username.required' => __('messages.field_required'),
            'username.max' => __('validation.max.string', ['max' => 255]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'platform' => __('messages.platform'),
            'username' => __('messages.username'),
            'display_name' => __('messages.display_name'),
        ];
    }
}
