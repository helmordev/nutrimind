<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_time_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('scope')->default('global');
            $table->uuid('scope_id')->nullable();
            $table->unsignedSmallInteger('school_day_limit_min')->default(45);
            $table->unsignedSmallInteger('weekend_limit_min')->default(60);
            $table->unsignedTinyInteger('max_levels_school')->default(2);
            $table->unsignedTinyInteger('max_levels_weekend')->default(3);
            $table->time('play_start_school')->default('15:00');
            $table->time('play_end_school')->default('20:00');
            $table->time('play_start_weekend')->default('08:00');
            $table->time('play_end_weekend')->default('20:00');
            $table->timestamps();

            $table->index(['scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_time_settings');
    }
};
