<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountGroupRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'exists:platform_accounts,id',
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
            'name.required' => __('messages.field_required'),
            'name.max' => __('validation.max.string', ['max' => 255]),
            'description.max' => __('validation.max.string', ['max' => 1000]),
            'account_ids.*.exists' => __('messages.invalid_account'),
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
            'name' => __('messages.group_name'),
            'description' => __('messages.group_description'),
            'account_ids' => __('messages.group_accounts'),
        ];
    }
}
