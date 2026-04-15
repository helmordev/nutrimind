<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('level_id')->constrained()->cascadeOnDelete();
            $table->string('question_type');
            $table->json('content');
            $table->json('correct_answer');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
