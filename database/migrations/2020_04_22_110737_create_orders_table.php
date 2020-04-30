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
            $table->string('uuid')->unique()->index();
            $table->string('gateway');
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->float('total');
            $table->float('item_total');
            $table->float('tax_total');
            $table->float('shipping_total');
            $table->float('coupon_total');
            $table->integer('currency_id')->index();
            $table->integer('order_status_id')->index();
            $table->integer('billing_address_id')->index()->nullable();
            $table->integer('shipping_address_id')->index()->nullable();
            $table->integer('customer_id')->index()->nullable();
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
