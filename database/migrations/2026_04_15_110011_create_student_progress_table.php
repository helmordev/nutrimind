<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('level_id')->constrained()->cascadeOnDelete();
            $table->string('difficulty_played')->default('standard');
            $table->decimal('score', 5, 2);
            $table->unsignedTinyInteger('stars');
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->unsignedSmallInteger('time_taken_seconds');
            $table->timestamp('completed_at');
            $table->string('local_id')->unique();
            $table->timestamps();

            $table->unique(['student_id', 'level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
