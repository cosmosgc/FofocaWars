<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedTinyInteger('level')->default(0);
            $table->timestamp('finishes_at')->nullable();
            $table->timestamps();

            $table->unique(['base_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_buildings');
    }
};
