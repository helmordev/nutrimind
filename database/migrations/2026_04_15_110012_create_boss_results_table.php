<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('boss_battle_id')->constrained()->cascadeOnDelete();
            $table->string('difficulty_played')->default('standard');
            $table->decimal('score', 5, 2);
            $table->unsignedSmallInteger('hp_dealt');
            $table->timestamp('completed_at');
            $table->string('local_id')->unique();
            $table->timestamps();

            $table->unique(['student_id', 'boss_battle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_results');
    }
};
