<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->integer('x');
            $table->integer('y');
            $table->string('terrain_type')->default('plain');
            $table->foreignId('owner_id')->nullable()->constrained('war_players')->nullOnDelete();
            $table->string('resource_type')->nullable();
            $table->string('structure_id')->nullable();
            $table->timestamps();

            $table->unique(['war_id', 'x', 'y']);
            $table->index(['war_id', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiles');
    }
};
