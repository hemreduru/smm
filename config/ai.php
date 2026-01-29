<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used when
    | generating topics. Supported: "openai", "claude", "gemini"
    |
    */

    'default' => env('AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each AI provider supported by
    | the application. Each provider requires specific API credentials.
    |
    */

    'providers' => [

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o'),
            'timeout' => env('OPENAI_TIMEOUT', 60),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 2048),
        ],

        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-5-sonnet-20241022'),
            'timeout' => env('ANTHROPIC_TIMEOUT', 60),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 2048),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-1.5-pro'),
            'timeout' => env('GEMINI_TIMEOUT', 60),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 2048),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Topic Generation Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to topic generation functionality.
    |
    */

    'topic_generation' => [
        'default_language' => env('AI_TOPIC_LANGUAGE', 'tr'),
        'suggestions_count' => env('AI_SUGGESTIONS_COUNT', 5),
        'temperature' => env('AI_TEMPERATURE', 0.8),
    ],

    /*
    |--------------------------------------------------------------------------
    | n8n Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for n8n webhook integration (self-hosted).
    |
    */

    'n8n' => [
        'base_url' => env('N8N_BASE_URL', 'http://localhost:5678'),
        'webhook_path' => env('N8N_WEBHOOK_PATH', '/webhook/topic'),
        'api_key' => env('N8N_API_KEY'),
        'timeout' => env('N8N_TIMEOUT', 30),
        'verify_ssl' => env('N8N_VERIFY_SSL', false), // For self-hosted
    ],

];
