<?php

namespace App\Http\Requests\Topic;

use Illuminate\Foundation\Http\FormRequest;

class StoreTopicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->current_workspace_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'niche' => ['nullable', 'string', 'max:100'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:50'],
            'is_scheduled' => ['nullable', 'boolean'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
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
            'title.required' => __('validation.required', ['attribute' => __('messages.topics.fields.title')]),
            'title.max' => __('validation.max.string', ['attribute' => __('messages.topics.fields.title'), 'max' => 255]),
            'description.max' => __('validation.max.string', ['attribute' => __('messages.topics.fields.description'), 'max' => 2000]),
            'scheduled_at.after' => __('validation.after', ['attribute' => __('messages.topics.fields.scheduled_at'), 'date' => __('messages.common.now')]),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert comma-separated keywords to array
        if ($this->has('keywords') && is_string($this->keywords)) {
            $keywords = array_filter(
                array_map('trim', explode(',', $this->keywords))
            );
            $this->merge(['keywords' => $keywords]);
        }
    }
}
