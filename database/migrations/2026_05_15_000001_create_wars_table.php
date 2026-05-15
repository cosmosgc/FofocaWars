<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('theme')->default('medieval');
            $table->integer('map_width')->default(200);
            $table->integer('map_height')->default(200);
            $table->decimal('resource_multiplier', 5, 2)->default(1.00);
            $table->decimal('troop_speed_multiplier', 5, 2)->default(1.00);
            $table->decimal('construction_speed', 5, 2)->default(1.00);
            $table->integer('max_bases_per_player')->default(3);
            $table->string('status')->default('setup');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wars');
    }
};
