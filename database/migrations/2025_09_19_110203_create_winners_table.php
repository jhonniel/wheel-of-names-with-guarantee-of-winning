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
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Track which admin recorded the winner
            $table->string('winner_name'); // Store the name at time of winning
            $table->string('winner_color')->nullable(); // Store the color at time of winning
            $table->decimal('spin_angle', 8, 2)->nullable(); // Store the spin angle
            $table->json('pool_snapshot')->nullable(); // Store the participants pool at time of spin
            $table->timestamp('won_at'); // When the winner was selected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
