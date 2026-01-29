<?php

namespace App\Enums;

enum TopicStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case SENT_TO_N8N = 'sent_to_n8n';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => __('messages.topic_status_draft'),
            self::APPROVED => __('messages.topic_status_approved'),
            self::SENT_TO_N8N => __('messages.topic_status_sent_to_n8n'),
            self::PROCESSING => __('messages.topic_status_processing'),
            self::COMPLETED => __('messages.topic_status_completed'),
            self::FAILED => __('messages.topic_status_failed'),
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'badge-light-secondary',
            self::APPROVED => 'badge-light-info',
            self::SENT_TO_N8N => 'badge-light-primary',
            self::PROCESSING => 'badge-light-warning',
            self::COMPLETED => 'badge-light-success',
            self::FAILED => 'badge-light-danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => 'bi-file-earmark',
            self::APPROVED => 'bi-check-circle',
            self::SENT_TO_N8N => 'bi-send',
            self::PROCESSING => 'bi-arrow-repeat',
            self::COMPLETED => 'bi-check2-all',
            self::FAILED => 'bi-x-circle',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => '#6c757d',
            self::APPROVED => '#0dcaf0',
            self::SENT_TO_N8N => '#0d6efd',
            self::PROCESSING => '#ffc107',
            self::COMPLETED => '#198754',
            self::FAILED => '#dc3545',
        };
    }

    /**
     * Check if topic can transition to a new status
     */
    public function canTransitionTo(TopicStatus $newStatus): bool
    {
        return match($this) {
            self::DRAFT => in_array($newStatus, [self::APPROVED]),
            self::APPROVED => in_array($newStatus, [self::DRAFT, self::SENT_TO_N8N]),
            self::SENT_TO_N8N => in_array($newStatus, [self::PROCESSING, self::FAILED]),
            self::PROCESSING => in_array($newStatus, [self::COMPLETED, self::FAILED]),
            self::COMPLETED => false, // Final state
            self::FAILED => in_array($newStatus, [self::DRAFT, self::APPROVED]), // Can retry
        };
    }

    /**
     * Get statuses that can be manually edited
     */
    public static function editableStatuses(): array
    {
        return [self::DRAFT, self::APPROVED, self::FAILED];
    }

    /**
     * Get statuses that can be sent to n8n
     */
    public static function sendableStatuses(): array
    {
        return [self::APPROVED];
    }
}
