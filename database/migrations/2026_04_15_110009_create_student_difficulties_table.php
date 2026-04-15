<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_difficulties', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->string('difficulty')->default('standard');
            $table->string('set_by')->default('teacher');
            $table->timestamp('updated_at_by_teacher')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_difficulties');
    }
};
