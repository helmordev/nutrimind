<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('difficulty_advisories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->string('current_difficulty');
            $table->string('suggested_difficulty');
            $table->text('reason');
            $table->decimal('rolling_avg', 5, 2);
            $table->boolean('is_reviewed')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('difficulty_advisories');
    }
};
