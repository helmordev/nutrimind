<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_battles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('quarter_id')->constrained()->cascadeOnDelete();
            $table->string('boss_name');
            $table->unsignedSmallInteger('total_hp')->default(500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_battles');
    }
};
