<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidColumnToCommerceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('states', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('order_statuses', function (Blueprint $table) {
            $table->string('uid')->unique();
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->string('uid')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commerce_tables', function (Blueprint $table) {
            //
        });
    }
}
