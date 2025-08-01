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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('user_level_id')->nullable();
            $table->decimal('min_trip_amount')->default(0);
            $table->decimal('max_coupon_amount')->default(0);
            $table->decimal('coupon')->default(0);
            $table->string('amount_type',15)->default('percentage');
            $table->string('coupon_type',15)->default('default');
            $table->string('coupon_code')->unique()->nullable();
            $table->integer('limit')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('rules')->nullable();
            $table->decimal('total_used')->default(0);
            $table->decimal('total_amount')->default(0);
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignUuid('role_id');
            $table->foreignUuid('user_id');
        });
    }
};
