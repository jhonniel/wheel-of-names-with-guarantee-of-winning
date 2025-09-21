<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wheel_settings', function (Blueprint $table) {
            $table->string('display_mode')->default('both')->after('spin_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_settings', function (Blueprint $table) {
            $table->dropColumn('display_mode');
        });
    }
};
