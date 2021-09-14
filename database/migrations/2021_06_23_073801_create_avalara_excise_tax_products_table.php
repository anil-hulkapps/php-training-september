<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvalaraExciseTaxProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avalara_excise_tax_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('shopify_product_id');
            $table->string('title');
            $table->string('handle');
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
        Schema::table('avalara_excise_tax_products', function (Blueprint $table) {
            $table->dropForeign('avalara_excise_tax_products_shop_id_foreign');
        });

        Schema::dropIfExists('avalara_excise_tax_products');
    }
}
