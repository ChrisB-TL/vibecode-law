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
        Schema::create('practice_area_showcase_draft', function (Blueprint $table) {
            $table->foreignId(column: 'practice_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'showcase_draft_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(columns: ['practice_area_id', 'showcase_draft_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_area_showcase_draft');
    }
};
