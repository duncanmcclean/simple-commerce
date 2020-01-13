<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAddressFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('default_billing_address_id')->nullable()->change();
            $table->integer('default_shipping_address_id')->nullable()->change();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('address2')->nullable()->change();
            $table->string('address3')->nullable()->change();
        });
    }
}
