<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('quarter_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level_number');
            $table->string('title');
            $table->string('matatag_competency_code')->nullable();
            $table->text('matatag_competency_desc')->nullable();
            $table->unsignedTinyInteger('unlock_week');
            $table->timestamps();

            $table->unique(['quarter_id', 'level_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
