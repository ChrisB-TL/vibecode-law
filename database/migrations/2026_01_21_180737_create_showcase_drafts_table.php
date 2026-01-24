<?php

use App\Enums\ShowcaseDraftStatus;
use App\Enums\SourceStatus;
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
        Schema::create('showcase_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_id')->unique()->constrained()->cascadeOnDelete();

            // Basic information
            $table->string('title', length: 100);
            $table->string('tagline');
            $table->text('description');
            $table->text('key_features')->nullable();
            $table->text('help_needed')->nullable();
            $table->string('url')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedTinyInteger('source_status')->default(SourceStatus::NotAvailable->value);
            $table->string('source_url')->nullable();
            $table->string('thumbnail_extension', 10)->nullable();
            $table->json('thumbnail_crop')->nullable();

            // Rich metadata
            $table->date('launch_date')->nullable();

            // Workflow
            $table->unsignedTinyInteger('status')->default(ShowcaseDraftStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Index for performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_drafts');
    }
};
