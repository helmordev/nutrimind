<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('language', 3)->default('en');
            $table->unsignedTinyInteger('master_volume')->default(80);
            $table->unsignedTinyInteger('bgm_volume')->default(70);
            $table->unsignedTinyInteger('sfx_volume')->default(90);
            $table->boolean('tts_enabled')->default(true);
            $table->string('text_size')->default('medium');
            $table->boolean('colorblind_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_preferences');
    }
};
