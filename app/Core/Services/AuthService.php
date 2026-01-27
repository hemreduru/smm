<?php

namespace App\Core\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Role;
use App\Core\Results\SuccessResult;
use App\Core\Results\FailResult;
use App\Core\Results\BaseResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthService extends BaseService
{
    public function login(array $credentials): BaseResult
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return new SuccessResult(__('messages.success'), ['user' => $user], route('dashboard'));
        }

        return new FailResult(__('auth.failed'), null, null, 401);
    }

    public function register(array $data): BaseResult
    {
        return DB::transaction(function () use ($data) {
            // Create User
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Create Default Workspace
            $workspaceName = $data['workspace_name'] ?? $user->name . "'s Workspace";
            $workspace = Workspace::create([
                'name' => $workspaceName,
                'slug' => \Illuminate\Support\Str::slug($workspaceName) . '-' . uniqid(),
                'owner_id' => $user->id,
            ]);

            // Create Admin Role for this Workspace
            $adminRole = Role::create([
                'workspace_id' => $workspace->id,
                'name' => 'Admin',
                'slug' => 'admin',
            ]);

            // Assign User to Workspace with Admin Role
            $user->workspaces()->attach($workspace->id, ['role_id' => $adminRole->id]);
            $user->current_workspace_id = $workspace->id;
            $user->save();

            Auth::login($user);

            return new SuccessResult(__('messages.success'), ['user' => $user], route('dashboard'));
        });
    }

    public function logout(): BaseResult
    {
        Auth::logout();
        return new SuccessResult(__('messages.success'), null, route('login'));
    }
}
