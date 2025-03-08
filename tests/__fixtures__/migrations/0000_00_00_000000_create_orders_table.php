<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('order_number')->autoIncrement();
            $table->timestamp('date');
            $table->string('site');
            $table->string('cart');
            $table->string('status');
            $table->string('customer');
            $table->string('coupon')->nullable();
            $table->bigInteger('grand_total');
            $table->bigInteger('sub_total');
            $table->bigInteger('discount_total');
            $table->bigInteger('tax_total');
            $table->bigInteger('shipping_total');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
