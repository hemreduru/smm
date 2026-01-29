<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');
        
        if (!$workspace instanceof Workspace) {
            return false;
        }

        // Only workspace owner can delete
        return $workspace->owner_id === Auth::id();
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
            __('messages.only_owner_can_delete_workspace')
        );
    }
}
