<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedTinyInteger('level')->default(0);
            $table->timestamp('finishes_at')->nullable();
            $table->timestamps();

            $table->unique(['city_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_buildings');
    }
};
