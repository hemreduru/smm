<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Platform account connection status.
 */
enum AccountStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';
    case ERROR = 'error';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('messages.status_active'),
            self::EXPIRED => __('messages.status_expired'),
            self::REVOKED => __('messages.status_revoked'),
            self::ERROR => __('messages.status_error'),
        };
    }

    /**
     * Get the badge class for the status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-light-success',
            self::EXPIRED => 'badge-light-warning',
            self::REVOKED => 'badge-light-danger',
            self::ERROR => 'badge-light-danger',
        };
    }

    /**
     * Check if the account is healthy (can be used for publishing).
     */
    public function isHealthy(): bool
    {
        return $this === self::ACTIVE;
    }
}
