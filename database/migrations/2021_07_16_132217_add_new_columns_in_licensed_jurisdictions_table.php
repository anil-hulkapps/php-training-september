<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsInLicensedJurisdictionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('licensed_jurisdictions', function (Blueprint $table) {
            $table->string('province')->nullable()->after('jurisdiction');
            $table->string('country_code')->nullable()->after('jurisdiction');
            $table->string('country')->nullable()->after('jurisdiction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licensed_jurisdictions', function (Blueprint $table) {
            //
        });
    }
}
