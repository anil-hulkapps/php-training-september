<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvalaraTransactionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avalara_transaction_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('ip')->nullable();
            $table->integer('status')->nullable();
            $table->integer('total_requested_products')->default(0);
            $table->json('request_data')->nullable();
            $table->json('filtered_request_data')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avalara_transaction_log');
    }
}
