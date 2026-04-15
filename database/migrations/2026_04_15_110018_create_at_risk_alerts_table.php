<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('at_risk_alerts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter_number');
            $table->decimal('grade_at_flag', 5, 2);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'quarter_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('at_risk_alerts');
    }
};
