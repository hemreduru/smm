<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Core\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected EmailVerificationService $verificationService
    ) {}

    /**
     * Show the email verification notice.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.verify-email');
    }

    /**
     * Handle email verification.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $result = $this->verificationService->verify($request->user(), $id, $hash);

        return $result->toResponse($request);
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $result = $this->verificationService->resend($request->user());

        return $result->toResponse($request);
    }
}
