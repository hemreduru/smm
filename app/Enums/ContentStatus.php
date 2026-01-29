<?php

namespace App\Enums;

enum ContentStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => __('messages.status_draft'),
            self::APPROVED => __('messages.status_approved'),
            self::SCHEDULED => __('messages.status_scheduled'),
            self::PUBLISHING => __('messages.status_publishing'),
            self::PUBLISHED => __('messages.status_published'),
            self::FAILED => __('messages.status_failed'),
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'badge-light-secondary',
            self::APPROVED => 'badge-light-info',
            self::SCHEDULED => 'badge-light-warning',
            self::PUBLISHING => 'badge-light-primary',
            self::PUBLISHED => 'badge-light-success',
            self::FAILED => 'badge-light-danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'bi-file-earmark',
            self::APPROVED => 'bi-check-circle',
            self::SCHEDULED => 'bi-clock',
            self::PUBLISHING => 'bi-arrow-repeat',
            self::PUBLISHED => 'bi-check2-all',
            self::FAILED => 'bi-x-circle',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => '#6c757d',
            self::APPROVED => '#0dcaf0',
            self::SCHEDULED => '#ffc107',
            self::PUBLISHING => '#0d6efd',
            self::PUBLISHED => '#198754',
            self::FAILED => '#dc3545',
        };
    }

    /**
     * Check if content can transition to a new status
     */
    public function canTransitionTo(ContentStatus $newStatus): bool
    {
        return match($this) {
            self::DRAFT => in_array($newStatus, [self::APPROVED, self::SCHEDULED]),
            self::APPROVED => in_array($newStatus, [self::DRAFT, self::SCHEDULED]),
            self::SCHEDULED => in_array($newStatus, [self::DRAFT, self::APPROVED, self::PUBLISHING]),
            self::PUBLISHING => in_array($newStatus, [self::PUBLISHED, self::FAILED]),
            self::PUBLISHED => false, // Final state
            self::FAILED => in_array($newStatus, [self::DRAFT, self::SCHEDULED]), // Can retry
        };
    }

    /**
     * Get all statuses that can be edited
     */
    public static function editableStatuses(): array
    {
        return [self::DRAFT, self::APPROVED, self::FAILED];
    }

    /**
     * Get all statuses that can be deleted
     */
    public static function deletableStatuses(): array
    {
        return [self::DRAFT, self::APPROVED, self::FAILED];
    }
}
