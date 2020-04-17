<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->integer('order_id')->index();
            $table->integer('variant_id')->index();
            $table->integer('tax_rate_id')->index();
            $table->integer('shipping_category_id')->index();
            $table->longText('description')->nullable();
            $table->string('sku');
            $table->float('price');
            $table->float('weight');
            $table->float('total');
            $table->integer('quantity');
            $table->longText('note')->nullable();
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
        Schema::dropIfExists('line_items');
    }
}
