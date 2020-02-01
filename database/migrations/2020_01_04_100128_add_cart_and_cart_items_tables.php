<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartAndCartItemsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('commerce.database_prefix').'carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->timestamps();
        });

        Schema::create(config('commerce.database_prefix').'cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->integer('quantity');
            $table->integer('product_id')->index();
            $table->integer('variant_id')->index();
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
        Schema::dropIfExists(config('commerce.database_prefix').'carts');
        Schema::dropIfExists(config('commerce.database_prefix').'cart_items');
    }
}
