<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('grade');
            $table->string('section');
            $table->string('room_code', 6)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
