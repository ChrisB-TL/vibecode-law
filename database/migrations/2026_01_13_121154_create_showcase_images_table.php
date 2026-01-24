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
        Schema::create('showcase_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('filename');
            $table->unsignedInteger('order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->index(['showcase_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_images');
    }
};
