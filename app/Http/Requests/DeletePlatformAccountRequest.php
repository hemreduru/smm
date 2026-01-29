<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\PlatformAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeletePlatformAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $account = $this->route('account');
        
        if (!$account instanceof PlatformAccount) {
            return false;
        }

        // User must be in the same workspace as the account
        return $account->workspace_id === Auth::user()->current_workspace_id;
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
