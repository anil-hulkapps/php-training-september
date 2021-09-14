<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->bigInteger('order_id');
            $table->string('order_number');
            $table->string('customer')->nullable();
            $table->integer('taxable_item')->nullable();
            $table->float('order_total')->default(0);
            $table->float('excise_tax')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamp('order_date')->nullable();
            $table->string('failed_reason')->nullable();
            $table->tinyInteger('is_ignore')->default(0);
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
        Schema::dropIfExists('transactions');
    }
}
