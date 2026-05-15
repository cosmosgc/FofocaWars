<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role')->default('infantry');
            $table->integer('attack')->default(10);
            $table->integer('defense')->default(10);
            $table->integer('speed')->default(10);
            $table->integer('wood_cost')->default(50);
            $table->integer('stone_cost')->default(30);
            $table->integer('food_cost')->default(40);
            $table->integer('metal_cost')->default(20);
            $table->integer('population_cost')->default(1);
            $table->integer('tier')->default(1);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_type_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['city_id', 'unit_type_id']);
        });

        Schema::create('armies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('war_players')->cascadeOnDelete();
            $table->foreignId('origin_city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('target_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('status')->default('stationed');
            $table->timestamp('arrival_at')->nullable();
            $table->timestamps();
            $table->index(['war_id', 'owner_id']);
        });

        Schema::create('army_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('army_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_type_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['army_id', 'unit_type_id']);
        });

        Schema::create('battle_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attacker_army_id')->constrained('armies')->cascadeOnDelete();
            $table->foreignId('defender_city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('winner')->nullable();
            $table->json('attacker_losses')->nullable();
            $table->json('defender_losses')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battle_reports');
        Schema::dropIfExists('army_units');
        Schema::dropIfExists('armies');
        Schema::dropIfExists('units');
        Schema::dropIfExists('unit_types');
    }
};
