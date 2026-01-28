<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50); // instagram, tiktok, youtube_shorts
            $table->string('platform_user_id')->nullable(); // External platform user ID
            $table->string('username');
            $table->string('display_name')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('status', 20)->default('active'); // active, expired, revoked, error
            $table->json('metadata')->nullable(); // Additional platform-specific data
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['workspace_id', 'platform']);
            $table->index('status');
            $table->unique(['workspace_id', 'platform', 'platform_user_id'], 'unique_platform_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_accounts');
    }
};
