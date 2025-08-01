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
        Schema::table('driver_behaviors', function (Blueprint $table) {
            $table->float('max_speed')->nullable()->after('harsh_braking_instances');
            $table->json('speeding_locations')->nullable()->after('max_speed');
            $table->json('harsh_braking_locations')->nullable()->after('speeding_locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_behaviors', function (Blueprint $table) {
            $table->dropColumn('max_speed');
            $table->dropColumn('speeding_locations');
            $table->dropColumn('harsh_braking_locations');
        });
    }
};
