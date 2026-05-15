<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->integer('training_time')->default(2)->after('tier');
        });

        Schema::create('training_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_type_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity')->default(0);
            $table->timestamp('finishes_at');
            $table->timestamps();
            $table->index(['city_id', 'finishes_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_queue');
        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropColumn('training_time');
        });
    }
};
