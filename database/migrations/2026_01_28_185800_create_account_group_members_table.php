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
        Schema::create('account_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Unique constraint to prevent duplicate memberships
            $table->unique(['account_group_id', 'platform_account_id'], 'unique_group_membership');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_group_members');
    }
};
