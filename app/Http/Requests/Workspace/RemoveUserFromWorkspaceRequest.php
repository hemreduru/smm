<?php

declare(strict_types=1);

namespace App\Http\Requests\Workspace;

use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RemoveUserFromWorkspaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspace = $this->route('workspace');
        $userId = $this->route('user');

        if (!$workspace instanceof Workspace) {
            return false;
        }

        // Only workspace owner can remove users
        // Cannot remove yourself (owner)
        return $workspace->owner_id === Auth::id() && $userId != Auth::id();
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
