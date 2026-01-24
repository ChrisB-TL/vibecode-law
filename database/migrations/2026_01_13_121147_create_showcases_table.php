<?php

use App\Enums\ShowcaseStatus;
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
        Schema::create('showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Basic information
            $table->string('title', length: 100);
            $table->string('slug', length: 110)->unique();
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

            // Approval workflow
            $table->unsignedTinyInteger('status')->default(ShowcaseStatus::Draft->value);
            $table->timestamp('submitted_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('approval_celebrated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            // Rich metadata
            $table->date('launch_date')->nullable();

            // Usage statistics
            $table->unsignedBigInteger('view_count')->default(0);

            // Featured flag
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status');
            $table->index('is_featured');
            $table->index(['status', 'submitted_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcases');
    }
};
