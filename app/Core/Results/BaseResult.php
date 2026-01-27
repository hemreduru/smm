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

    protected function isJson(Request $request): bool
    {
        return $request->expectsJson();
    }
}
