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
        Schema::create('showcase_draft_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_image_id')
                ->nullable()
                ->constrained('showcase_images')
                ->nullOnDelete();
            $table->string('action'); // 'keep', 'add', 'remove'
            $table->string('path')->nullable(); // For new images
            $table->string('filename')->nullable(); // For new images
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['showcase_draft_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_draft_images');
    }
};
