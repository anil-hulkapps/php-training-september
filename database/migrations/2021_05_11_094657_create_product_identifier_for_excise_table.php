<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductIdentifierForExciseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_identifier_for_excise', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->tinyInteger('identifier')->nullable();
            $table->tinyInteger('option')->nullable();
            $table->string('value', 255)->nullable();
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
        Schema::dropIfExists('product_identifier_for_excise');
    }
}
