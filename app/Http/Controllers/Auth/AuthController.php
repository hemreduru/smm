<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Core\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated())->toResponse($request);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request->validated())->toResponse($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout()->toResponse($request);
    }
}
