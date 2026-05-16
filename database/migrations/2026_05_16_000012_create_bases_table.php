<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('war_players')->cascadeOnDelete();
            $table->unsignedInteger('tile_x');
            $table->unsignedInteger('tile_y');
            $table->string('type'); // resource, military, trade, alliance
            $table->string('name');
            $table->unsignedTinyInteger('level')->default(1);
            $table->timestamps();

            $table->unique(['war_id', 'tile_x', 'tile_y']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bases');
    }
};
