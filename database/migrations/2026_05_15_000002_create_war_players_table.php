<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('war_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamps();

            $table->unique(['war_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_players');
    }
};
