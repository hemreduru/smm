<?php

namespace App\Http\Requests\Topic;

use App\Enums\AIProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GenerateTopicRequest extends FormRequest
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
            'niche' => ['required', 'string', 'max:100'],
            'keywords' => ['nullable', 'array', 'max:10'],
            'keywords.*' => ['string', 'max:50'],
            'provider' => ['nullable', 'string', new Enum(AIProvider::class)],
            'model' => ['nullable', 'string', 'max:100'],
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
            'niche.required' => __('messages.topics.validation.niche_required'),
            'niche.max' => __('validation.max.string', ['attribute' => __('messages.topics.fields.niche'), 'max' => 100]),
            'keywords.max' => __('messages.topics.validation.max_keywords'),
            'provider.enum' => __('messages.topics.validation.invalid_provider'),
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
            $this->merge(['keywords' => array_slice($keywords, 0, 10)]);
        }
    }
}
