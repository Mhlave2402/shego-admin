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
        Schema::create('driver_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('trip_id')->constrained('trip_requests')->onDelete('cascade');
            $table->integer('speeding_instances')->default(0);
            $table->integer('harsh_braking_instances')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_behaviors');
    }
};
