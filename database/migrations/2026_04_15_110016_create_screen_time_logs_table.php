<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_time_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedSmallInteger('total_minutes')->default(0);
            $table->unsignedTinyInteger('levels_played')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_time_logs');
    }
};
