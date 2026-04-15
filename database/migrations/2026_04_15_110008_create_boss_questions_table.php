<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_questions', function (Blueprint $table): void {
            $table->foreignUuid('boss_battle_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained()->cascadeOnDelete();

            $table->primary(['boss_battle_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_questions');
    }
};
