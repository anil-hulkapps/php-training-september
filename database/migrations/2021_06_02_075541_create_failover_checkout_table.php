<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailoverCheckoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failover_checkout', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->tinyInteger("action")->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('identifier')->nullable();
            $table->string('value')->nullable();
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
        Schema::dropIfExists('failover_checkout');
    }
}
