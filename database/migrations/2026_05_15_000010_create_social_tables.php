<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('tag', 10);
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->constrained('war_players')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['war_id', 'tag']);
        });

        Schema::create('alliance_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alliance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['alliance_id', 'war_player_id']);
        });

        Schema::create('diplomacy_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alliance_id_1')->constrained('alliances')->cascadeOnDelete();
            $table->foreignId('alliance_id_2')->constrained('alliances')->cascadeOnDelete();
            $table->string('type')->default('neutral');
            $table->foreignId('proposed_by')->nullable()->constrained('alliance_members')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['alliance_id_1', 'alliance_id_2']);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained()->cascadeOnDelete();
            $table->unique(['conversation_id', 'war_player_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('war_players')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_id')->constrained()->cascadeOnDelete();
            $table->foreignId('war_player_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['war_player_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('diplomacy_relations');
        Schema::dropIfExists('alliance_members');
        Schema::dropIfExists('alliances');
    }
};
