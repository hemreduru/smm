<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Results\BaseResult;
use App\Core\Results\SuccessResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

class EmailVerificationService extends BaseService
{
    /**
     * Verify user's email.
     */
    public function verify(MustVerifyEmail $user, int $id, string $hash): BaseResult
    {
        try {
            // Check if user ID matches
            if ($user->id !== $id) {
                Log::warning("EmailVerification: ID mismatch - User: {$user->id} tried to verify ID: {$id}");

                return new FailResult(
                    __('messages.verification_failed'),
                    route('dashboard')
                );
            }

            // Check hash
            if (!hash_equals(sha1($user->email), $hash)) {
                Log::warning("EmailVerification: Hash mismatch - User: {$user->id}");

                return new FailResult(
                    __('messages.verification_failed'),
                    route('dashboard')
                );
            }

            // Already verified
            if ($user->hasVerifiedEmail()) {
                return new SuccessResult(
                    __('messages.email_already_verified'),
                    null,
                    route('dashboard')
                );
            }

            // Mark as verified
            $user->markEmailAsVerified();
            event(new Verified($user));

            Log::info("EmailVerification: Email verified - User: {$user->id}");

            return new SuccessResult(
                __('messages.email_verified'),
                null,
                route('dashboard')
            );
        } catch (\Exception $e) {
            Log::error("EmailVerification: Exception - User: {$user->id} - {$e->getMessage()}");

            return new ServerErrorResult(
                __('messages.server_error'),
                route('dashboard')
            );
        }
    }

    /**
     * Resend verification email.
     */
    public function resend(MustVerifyEmail $user): BaseResult
    {
        try {
            if ($user->hasVerifiedEmail()) {
                return new SuccessResult(
                    __('messages.email_already_verified'),
                    null,
                    route('dashboard')
                );
            }

            $user->sendEmailVerificationNotification();

            Log::info("EmailVerification: Verification email resent - User: {$user->id}");

            return new SuccessResult(
                __('messages.verification_link_sent'),
                null,
                route('verification.notice')
            );
        } catch (\Exception $e) {
            Log::error("EmailVerification: Resend failed - User: {$user->id} - {$e->getMessage()}");

            return new ServerErrorResult(
                __('messages.server_error'),
                route('verification.notice')
            );
        }
    }
}
