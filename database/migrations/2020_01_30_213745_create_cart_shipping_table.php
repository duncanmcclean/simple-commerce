<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartShippingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('commerce.database_prefix').'cart_shipping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->integer('shipping_zone_id')->index();
            $table->integer('cart_id')->index();
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
        Schema::dropIfExists(config('commerce.database_prefix').'cart_shipping');
    }
}
