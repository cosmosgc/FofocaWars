<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained('war_players')->cascadeOnDelete();
            $table->bigInteger('wood')->default(0);
            $table->bigInteger('stone')->default(0);
            $table->bigInteger('food')->default(0);
            $table->bigInteger('metal')->default(0);
            $table->bigInteger('total')->default(0);
            $table->timestamp('recorded_at');
            $table->index(['war_id', 'recorded_at']);
            $table->index(['war_id', 'war_player_id', 'recorded_at']);
        });

        Schema::create('army_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained('war_players')->cascadeOnDelete();
            $table->integer('total_units')->default(0);
            $table->float('attack_power')->default(0);
            $table->float('defense_power')->default(0);
            $table->timestamp('recorded_at');
            $table->index(['war_id', 'recorded_at']);
            $table->index(['war_id', 'war_player_id', 'recorded_at']);
        });

        Schema::create('territory_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained('war_players')->cascadeOnDelete();
            $table->integer('city_count')->default(0);
            $table->integer('base_count')->default(0);
            $table->integer('tile_count')->default(0);
            $table->timestamp('recorded_at');
            $table->index(['war_id', 'recorded_at']);
            $table->index(['war_id', 'war_player_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territory_history');
        Schema::dropIfExists('army_history');
        Schema::dropIfExists('resource_history');
    }
};
