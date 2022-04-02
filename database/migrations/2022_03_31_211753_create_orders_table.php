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
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_shipped')->default(false);
            $table->boolean('is_refunded')->default(false);
            $table->json('items')->nullable();
            $table->integer('grand_total')->default(0);
            $table->integer('items_total')->default(0);
            $table->integer('tax_total')->default(0);
            $table->integer('shipping_total')->default(0);
            $table->integer('coupon_total')->default(0);
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
