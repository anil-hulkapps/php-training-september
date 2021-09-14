<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmColumnToProductIdentifierForExciseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_identifier_for_excise', function (Blueprint $table) {
            $table->tinyInteger('confirm')->after('value')->comment('1: agree by product code excise calc; 0: disagree')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_identifier_for_excise', function (Blueprint $table) {
            $table->dropColumn('confirm');
        });
    }
}
