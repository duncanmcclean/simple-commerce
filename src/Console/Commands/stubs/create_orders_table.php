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
            $table->id();
            $table->bigInteger('order_number')->unique()->nullable();
            $table->string('order_status')->default('cart');
            $table->string('payment_status')->default('unpaid');
            $table->json('items')->nullable();
            $table->integer('grand_total')->default(0);
            $table->integer('items_total')->default(0);
            $table->integer('tax_total')->default(0);
            $table->integer('shipping_total')->default(0);
            $table->integer('coupon_total')->default(0);
            $table->string('shipping_name')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('shipping_address_line2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_region')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('billing_name')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('billing_address_line2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_region')->nullable();
            $table->string('billing_country')->nullable();
            $table->boolean('use_shipping_address_for_billing')->default(false);
            $table->foreignId('customer_id')->nullable();
            $table->string('coupon')->nullable();
            $table->json('gateway')->nullable();
            $table->json('data')->nullable();
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
