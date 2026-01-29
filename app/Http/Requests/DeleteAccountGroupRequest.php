<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\AccountGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteAccountGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');
        
        if (!$group instanceof AccountGroup) {
            return false;
        }

        // User must be in the same workspace as the group
        return $group->workspace_id === Auth::user()->current_workspace_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom messages for authorization failure.
     */
    public function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            __('messages.unauthorized_action')
        );
    }
}
