<?php

namespace App\Enums;

enum AIProvider: string
{
    case OPENAI = 'openai';
    case CLAUDE = 'claude';
    case GEMINI = 'gemini';

    public function label(): string
    {
        return match($this) {
            self::OPENAI => 'OpenAI (GPT)',
            self::CLAUDE => 'Anthropic Claude',
            self::GEMINI => 'Google Gemini',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::OPENAI => 'bi-stars',
            self::CLAUDE => 'bi-robot',
            self::GEMINI => 'bi-google',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPENAI => '#10a37f',
            self::CLAUDE => '#d97706',
            self::GEMINI => '#4285f4',
        };
    }

    public function defaultModel(): string
    {
        return match($this) {
            self::OPENAI => 'gpt-4o',
            self::CLAUDE => 'claude-3-5-sonnet-20241022',
            self::GEMINI => 'gemini-1.5-pro',
        };
    }

    /**
     * Get available models for this provider
     */
    public function availableModels(): array
    {
        return match($this) {
            self::OPENAI => [
                'gpt-4o' => 'GPT-4o (Recommended)',
                'gpt-4o-mini' => 'GPT-4o Mini (Faster)',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Economy)',
            ],
            self::CLAUDE => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (Recommended)',
                'claude-3-opus-20240229' => 'Claude 3 Opus (Most Capable)',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku (Fastest)',
            ],
            self::GEMINI => [
                'gemini-1.5-pro' => 'Gemini 1.5 Pro (Recommended)',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Faster)',
                'gemini-pro' => 'Gemini Pro',
            ],
        };
    }
}
