<?php

declare(strict_types=1);

namespace App\Core\Services;

use App\Core\Results\BaseResult;
use App\Core\Results\SuccessResult;
use App\Core\Results\FailResult;
use App\Core\Results\ServerErrorResult;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetService extends BaseService
{
    /**
     * Send password reset link to user's email.
     *
     * @param array<string, mixed> $data
     */
    public function sendResetLink(array $data): BaseResult
    {
        try {
            $status = Password::sendResetLink(['email' => $data['email']]);

            Log::info("PasswordReset: SendLink attempted for email: {$data['email']}");

            if ($status === Password::RESET_LINK_SENT) {
                return new SuccessResult(
                    __('passwords.sent'),
                    null,
                    route('login')
                );
            }

            return new FailResult(
                __($status),
                route('password.request')
            );
        } catch (\Exception $e) {
            Log::error("PasswordReset: SendLink failed - {$e->getMessage()}");

            return new ServerErrorResult(
                __('messages.server_error'),
                route('password.request')
            );
        }
    }

    /**
     * Reset user's password.
     *
     * @param array<string, mixed> $data
     */
    public function resetPassword(array $data): BaseResult
    {
        DB::beginTransaction();

        try {
            $status = Password::reset(
                [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'password_confirmation' => $data['password'],
                    'token' => $data['token'],
                ],
                function (User $user, string $password): void {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                DB::commit();

                Log::info("PasswordReset: Password reset successful for email: {$data['email']}");

                return new SuccessResult(
                    __('passwords.reset'),
                    null,
                    route('login')
                );
            }

            DB::rollBack();

            Log::warning("PasswordReset: Reset failed for email: {$data['email']} - Status: {$status}");

            return new FailResult(
                __($status),
                route('password.reset', ['token' => $data['token'], 'email' => $data['email']])
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("PasswordReset: Reset exception for email: {$data['email']} - {$e->getMessage()}");

            return new ServerErrorResult(
                __('messages.server_error'),
                route('password.request')
            );
        }
    }
}
