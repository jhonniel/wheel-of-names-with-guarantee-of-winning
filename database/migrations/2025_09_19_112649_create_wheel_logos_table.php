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
        Schema::create('wheel_logos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Optional name for the logo
            $table->string('image_path'); // Path to the uploaded logo image
            $table->boolean('is_active')->default(false); // Only one logo can be active at a time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wheel_logos');
    }
};
