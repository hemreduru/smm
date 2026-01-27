<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Demo User
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'demo@demo.com'],
            [
                'name' => 'Demo User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // 2. Create Demo Workspace
        $workspace = \App\Models\Workspace::firstOrCreate(
            ['slug' => 'demo-workspace'],
            [
                'name' => 'Demo Workspace',
                'owner_id' => $user->id,
            ]
        );

        // 3. Create Roles for Workspace (Use updateOrCreate or check exist)
        $adminRole = \App\Models\Role::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'admin'],
            ['name' => 'Admin']
        );
        
        \App\Models\Role::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'editor'],
            ['name' => 'Editor']
        );

        \App\Models\Role::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'viewer'],
            ['name' => 'Viewer']
        );

        // 4. Attach User to Workspace with Admin Role if not attached
        if (!$user->workspaces()->where('workspace_id', $workspace->id)->exists()) {
             $user->workspaces()->attach($workspace->id, ['role_id' => $adminRole->id]);
        }
        
        $user->current_workspace_id = $workspace->id;
        $user->save();
    }
}
