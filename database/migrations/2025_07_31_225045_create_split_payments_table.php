<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('split_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('trip_request_id');
            $table->uuid('user_id');
            $table->decimal('amount', 8, 2);
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->timestamps();

            $table->foreign('trip_request_id')->references('id')->on('trip_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('split_payments');
    }
};
