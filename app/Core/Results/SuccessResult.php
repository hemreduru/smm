<?php

namespace App\Core\Results;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SuccessResult extends BaseResult
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        if ($this->isJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $this->message,
                'data' => $this->data,
            ], $this->statusCode);
        }

        $redirect = $this->redirectUrl ? redirect()->to($this->redirectUrl) : back();
        
        return $redirect->with('success', $this->message)
                        ->with('data', $this->data);
    }
}
