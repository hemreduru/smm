<?php

namespace App\Http\Requests;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;

class DeleteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $content = $this->route('content');

        if (!$content instanceof Content) {
            $content = Content::find($this->route('content'));
        }

        if (!$content) {
            return false;
        }

        // Check workspace ownership
        if ($content->workspace_id !== auth()->user()->current_workspace_id) {
            return false;
        }

        // Check if content can be deleted
        return $content->isDeletable();
    }

    public function rules(): array
    {
        return [];
    }
}
