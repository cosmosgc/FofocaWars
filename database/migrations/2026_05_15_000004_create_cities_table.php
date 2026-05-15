<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('war_players')->cascadeOnDelete();
            $table->string('name');
            $table->integer('tile_x');
            $table->integer('tile_y');
            $table->integer('population')->default(100);
            $table->bigInteger('wood')->default(500);
            $table->bigInteger('stone')->default(500);
            $table->bigInteger('food')->default(500);
            $table->bigInteger('metal')->default(500);
            $table->timestamps();

            $table->index(['war_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
