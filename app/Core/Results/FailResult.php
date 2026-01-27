<?php

namespace App\Core\Results;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;

class FailResult extends BaseResult
{
    public function __construct(
        string $message,
        mixed $errors = null,
        ?string $redirectUrl = null,
        int $statusCode = 400 // Bad Request default
    ) {
        parent::__construct($message, $errors, $redirectUrl, $statusCode);
    }

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($this->isJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $this->message,
                'errors' => $this->data,
            ], $this->statusCode);
        }

        $redirect = $this->redirectUrl ? redirect()->to($this->redirectUrl) : back();

        // If data contains validation errors (MessageBag or array), flash them to errors
        if ($this->data instanceof MessageBag || is_array($this->data)) {
            $redirect->withErrors($this->data);
        }

        return $redirect->with('error', $this->message)->withInput();
    }
}
