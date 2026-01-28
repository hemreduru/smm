<?php

namespace App\Core\Results;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class BaseResult implements Responsable
{
    public function __construct(
        protected string $message,
        protected mixed $data = null,
        protected ?string $redirectUrl = null,
        protected int $statusCode = 200
    ) {}

    abstract public function toResponse($request);

    /**
     * Convert the result to an array for session flash storage.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'message' => $this->message,
            'data' => $this->data,
            'statusCode' => $this->statusCode,
        ];
    }

    /**
     * Get the result type (success, fail, error).
     */
    abstract protected function getType(): string;

    protected function isJson(Request $request): bool
    {
        return $request->expectsJson();
    }
}
