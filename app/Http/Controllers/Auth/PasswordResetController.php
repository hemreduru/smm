<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Core\Services\PasswordResetService;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {}

    /**
     * Show forgot password form.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $result = $this->passwordResetService->sendResetLink($request->validated());
        
        return $result->toResponse($request);
    }

    /**
     * Show reset password form.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Reset password.
     */
    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $result = $this->passwordResetService->resetPassword($request->validated());
        
        return $result->toResponse($request);
    }
}
