<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id');
            $table->bigInteger('variant_id');
            $table->string('option_1_name')->nullable();
            $table->string('option_1_value')->nullable();
            $table->string('option_2_name')->nullable();
            $table->string('option_2_value')->nullable();
            $table->string('option_3_name')->nullable();
            $table->string('option_3_value')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->float('price')->nullable()->default(0);
            $table->float('compare_at_price')->nullable()->default(0);
            $table->integer('quantity')->default(0);
            $table->unsignedBigInteger('shop_id');
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
        Schema::dropIfExists('product_variants');
    }
}
