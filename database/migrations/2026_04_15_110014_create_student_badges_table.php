<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_badges', function (Blueprint $table): void {
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');

            $table->primary(['student_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_badges');
    }
};
