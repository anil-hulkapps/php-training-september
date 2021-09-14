<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExciseByProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excise_by_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->bigInteger('product_id');
            $table->float('excise_tax')->nullable()->default(0);
            $table->date('date')->index();

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
        Schema::dropIfExists('excise_by_product');
    }
}
