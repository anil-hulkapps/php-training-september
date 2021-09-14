<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_infos', function (Blueprint $table) {
            $table->id();
            $table->string('country_code')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->string('product_code')->nullable();
            $table->string('description')->nullable();
            $table->string('alternate_product_code');
            $table->string('terminal_code')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('alternate_effective_date')->nullable();
            $table->string('alternate_obsolete_date')->nullable();
            $table->string('product_effective_date')->nullable();
            $table->string('product_obsolete_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_infos');
    }
}
