<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_intent');
            $table->integer('billing_address_id')->index();
            $table->integer('shipping_address_id')->index();
            $table->integer('customer_id')->index();
            $table->integer('order_status_id')->index();
            $table->json('items');
            $table->string('total');
            $table->boolean('is_completed');
            $table->boolean('is_paid');
            $table->integer('currency_id')->index();
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
        Schema::dropIfExists('orders');
    }
}
