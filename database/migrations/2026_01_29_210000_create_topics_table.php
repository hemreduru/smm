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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Topic Content
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('niche'); // Category/niche for the topic
            $table->json('keywords')->nullable(); // Related keywords

            // AI Generation
            $table->string('ai_provider')->nullable(); // openai, claude, gemini
            $table->string('ai_model')->nullable(); // specific model used
            $table->text('ai_prompt')->nullable(); // The prompt sent to AI
            $table->text('ai_response')->nullable(); // Full AI response

            // Status Tracking
            $table->string('status')->default('draft'); // TopicStatus enum

            // n8n Integration
            $table->string('n8n_execution_id')->nullable(); // n8n workflow execution ID
            $table->timestamp('sent_at')->nullable(); // When sent to n8n
            $table->timestamp('completed_at')->nullable(); // When video completed

            // Error Handling
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();

            // Scheduling
            $table->boolean('is_scheduled')->default(false);
            $table->timestamp('scheduled_at')->nullable(); // For scheduled sending

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('niche');
            $table->index(['workspace_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
