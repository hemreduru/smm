<?php

namespace App\Core\AI\Adapters;

use App\Core\AI\Contracts\AIProviderInterface;
use App\Core\AI\Contracts\AIResponse;
use App\Enums\AIProvider;
use Illuminate\Support\Facades\Log;

/**
 * Base class for AI provider adapters.
 */
abstract class BaseAIAdapter implements AIProviderInterface
{
    protected string $model;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->model = $config['default_model'] ?? $this->getDefaultModel();
    }

    abstract protected function getDefaultModel(): string;
    abstract protected function getProvider(): AIProvider;

    public function getProviderName(): string
    {
        return $this->getProvider()->value;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Build the topic generation prompt.
     */
    protected function buildTopicPrompt(string $niche, array $keywords = [], ?string $customPrompt = null): string
    {
        if ($customPrompt) {
            return $customPrompt;
        }

        $language = config('ai.topic_generation.default_language', 'tr');
        $count = config('ai.topic_generation.suggestions_count', 5);

        $keywordText = !empty($keywords)
            ? "Anahtar kelimeler: " . implode(', ', $keywords) . "\n"
            : '';

        $languageInstruction = $language === 'tr'
            ? 'Yanıtı Türkçe olarak ver.'
            : 'Respond in English.';

        return <<<PROMPT
Sen bir sosyal medya içerik uzmanısın. Verilen niş için viral olabilecek kısa video içerik konuları öner.

Niş/Kategori: {$niche}
{$keywordText}

Kurallar:
1. {$count} adet konu öner
2. Konular TikTok, Instagram Reels ve YouTube Shorts için uygun olmalı
3. Her konu 60 saniyeden kısa videolar için ideal olmalı
4. Dikkat çekici ve ilgi uyandırıcı konular seç
5. {$languageInstruction}

Yanıtını şu JSON formatında ver:
```json
[
  {
    "title": "Konu başlığı",
    "description": "Kısa açıklama (1-2 cümle)",
    "hook": "Video için dikkat çekici açılış cümlesi",
    "keywords": ["anahtar", "kelimeler"]
  }
]
```

Sadece JSON döndür, başka açıklama ekleme.
PROMPT;
    }

    /**
     * Build single topic generation prompt.
     */
    protected function buildSingleTopicPrompt(string $niche, ?string $context = null): string
    {
        $language = config('ai.topic_generation.default_language', 'tr');

        $contextText = $context ? "Ek Bağlam: {$context}\n" : '';

        $languageInstruction = $language === 'tr'
            ? 'Yanıtı Türkçe olarak ver.'
            : 'Respond in English.';

        return <<<PROMPT
Sen bir sosyal medya içerik uzmanısın. Verilen niş için tek bir viral video konusu oluştur.

Niş/Kategori: {$niche}
{$contextText}

Kurallar:
1. TikTok, Instagram Reels ve YouTube Shorts için uygun olmalı
2. 60 saniyeden kısa video için ideal olmalı
3. Dikkat çekici ve ilgi uyandırıcı olmalı
4. {$languageInstruction}

Yanıtını şu JSON formatında ver:
```json
{
  "title": "Konu başlığı",
  "description": "Detaylı açıklama (video içeriği hakkında)",
  "hook": "Video için dikkat çekici açılış cümlesi",
  "script_outline": ["Adım 1", "Adım 2", "Adım 3"],
  "keywords": ["anahtar", "kelimeler"],
  "estimated_duration": "30-45 saniye"
}
```

Sadece JSON döndür, başka açıklama ekleme.
PROMPT;
    }

    /**
     * Log AI request for debugging.
     */
    protected function logRequest(string $action, array $data = []): void
    {
        Log::info("[AI:{$this->getProviderName()}] {$action}", array_merge([
            'model' => $this->model,
        ], $data));
    }

    /**
     * Log AI error.
     */
    protected function logError(string $action, string $error, array $data = []): void
    {
        Log::error("[AI:{$this->getProviderName()}] {$action} failed", array_merge([
            'model' => $this->model,
            'error' => $error,
        ], $data));
    }
}
