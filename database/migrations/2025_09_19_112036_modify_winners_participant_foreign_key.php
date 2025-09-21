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
        Schema::table('winners', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['participant_id']);

            // Make participant_id nullable
            $table->unsignedBigInteger('participant_id')->nullable()->change();

            // Add a new foreign key constraint that doesn't cascade delete
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            // Drop the current foreign key constraint
            $table->dropForeign(['participant_id']);

            // Make participant_id not nullable again
            $table->unsignedBigInteger('participant_id')->nullable(false)->change();

            // Restore the original cascade delete constraint
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
        });
    }
};
