<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('items');
            $table->dropColumn('is_refunded');

            $table->string('gateway');
            $table->float('item_total');
            $table->float('tax_total');
            $table->float('shipping_total');
            $table->float('total')->change()->charset(null);
            $table->boolean('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
