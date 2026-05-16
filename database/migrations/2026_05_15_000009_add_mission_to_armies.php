<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('armies', function (Blueprint $table) {
            $table->string('mission')->default('attack')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('armies', function (Blueprint $table) {
            $table->dropColumn('mission');
        });
    }
};
