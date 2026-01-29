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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Media
            $table->string('video_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->unsignedInteger('duration')->nullable(); // seconds

            // Captions (multi-language support)
            $table->text('caption_tr')->nullable();
            $table->text('caption_en')->nullable();

            // Hashtags stored as JSON array
            $table->json('hashtags')->nullable();

            // Status & Scheduling
            $table->string('status')->default('draft'); // ContentStatus enum
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Metadata
            $table->string('title')->nullable();
            $table->text('notes')->nullable(); // Internal notes

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'scheduled_at']);
            $table->index(['workspace_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
