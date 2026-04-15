<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarters', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter_number');
            $table->unsignedTinyInteger('current_unlock_week')->default(0);
            $table->boolean('is_globally_unlocked')->default(false);
            $table->timestamps();

            $table->unique(['subject_id', 'quarter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
