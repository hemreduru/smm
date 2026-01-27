<?php

namespace App\Core\Results;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ServerErrorResult extends BaseResult
{
    public function __construct(
        ?string $message = null,
        ?string $debugMessage = null, // Optional for internal logging logic if needed, but rarely exposed
        ?string $redirectUrl = null,
        int $statusCode = 500
    ) {
        $message = $message ?? __('messages.server_error');
        // In production, might want to mask debugMessage, but we rely on Handler or usage to determine message content.
        parent::__construct($message, $debugMessage, $redirectUrl, $statusCode);
    }

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($this->isJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $this->message, // Standardized generic error or safe message
                // 'debug' => $this->data // Optional: only if App::hasDebugModeEnabled()
            ], $this->statusCode);
        }

        $redirect = $this->redirectUrl ? redirect()->to($this->redirectUrl) : back();
        
        return $redirect->with('error', $this->message);
    }
}
