<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SomeFieldsShouldBeNullableOnOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_intent')->nullable()->change();
            $table->integer('billing_address_id')->nullable()->change();
            $table->integer('shipping_address_id')->nullable()->change();
        });
    }
}
