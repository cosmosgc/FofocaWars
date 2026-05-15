<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->bigInteger('max_wood')->default(10000)->after('wood');
            $table->bigInteger('max_stone')->default(10000)->after('max_wood');
            $table->bigInteger('max_food')->default(10000)->after('max_stone');
            $table->bigInteger('max_metal')->default(10000)->after('max_food');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['max_wood', 'max_stone', 'max_food', 'max_metal']);
        });
    }
};
