<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Supported social media platforms.
 */
enum Platform: string
{
    case INSTAGRAM = 'instagram';
    case TIKTOK = 'tiktok';
    case YOUTUBE_SHORTS = 'youtube_shorts';

    /**
     * Get a human-readable label for the platform.
     */
    public function label(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'Instagram',
            self::TIKTOK => 'TikTok',
            self::YOUTUBE_SHORTS => 'YouTube Shorts',
        };
    }

    /**
     * Get the icon class for the platform.
     */
    public function icon(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'bi-instagram',
            self::TIKTOK => 'bi-tiktok',
            self::YOUTUBE_SHORTS => 'bi-youtube',
        };
    }

    /**
     * Get the brand color for the platform.
     */
    public function color(): string
    {
        return match ($this) {
            self::INSTAGRAM => '#E4405F',
            self::TIKTOK => '#000000',
            self::YOUTUBE_SHORTS => '#FF0000',
        };
    }

    /**
     * Get the badge class for the platform.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::INSTAGRAM => 'badge-light-danger',
            self::TIKTOK => 'badge-light-dark',
            self::YOUTUBE_SHORTS => 'badge-light-danger',
        };
    }

    /**
     * Get all platforms as an array for select options.
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($platform) => [
            $platform->value => $platform->label(),
        ])->toArray();
    }
}
