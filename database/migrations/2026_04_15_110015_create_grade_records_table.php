<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter_number');
            $table->decimal('written_work', 5, 2)->nullable();
            $table->decimal('performance_task', 5, 2)->nullable();
            $table->decimal('quarterly_assessment', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'quarter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_records');
    }
};
