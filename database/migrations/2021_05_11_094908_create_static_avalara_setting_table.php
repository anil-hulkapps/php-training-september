<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaticAvalaraSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('static_avalara_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('field', 255)->nullable();
            $table->string('value')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1: parent; 2: order; 3:lineitem');
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
        Schema::dropIfExists('static_avalara_setting');
    }
}
