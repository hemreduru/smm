<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->current_workspace_id !== null;
    }

    public function rules(): array
    {
        return [
            'video' => ['required', 'file', 'mimes:mp4,mov,avi,webm', 'max:512000'], // 500MB max
            'title' => ['nullable', 'string', 'max:255'],
            'caption_tr' => ['nullable', 'string', 'max:2200'], // Instagram limit
            'caption_en' => ['nullable', 'string', 'max:2200'],
            'hashtags' => ['nullable', 'string', 'max:1000'],
            'account_group_id' => ['nullable', 'exists:account_groups,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => __('messages.video_required'),
            'video.mimes' => __('messages.video_invalid_format'),
            'video.max' => __('messages.video_too_large'),
            'scheduled_at.after' => __('messages.scheduled_at_must_be_future'),
        ];
    }
}
