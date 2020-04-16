<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAndRemoveFromShippingZones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->string('name');

            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
            $table->dropColumn('start_of_zip_code');
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_zones', function (Blueprint $table) {
            //
        });
    }
}
