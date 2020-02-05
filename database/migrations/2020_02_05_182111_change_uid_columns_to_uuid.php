<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUidColumnsToUuid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('order_statuses', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('cart_shipping', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });

        Schema::table('cart_taxes', function (Blueprint $table) {
            $table->renameColumn('uid', 'uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('variants', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('order_statuses', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('cart_shipping', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });

        Schema::table('cart_taxes', function (Blueprint $table) {
            $table->renameColumn('uuid', 'uid');
        });
    }
}