<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('commerce.database_prefix').'variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->string('sku');
            $table->string('name');
            $table->string('price');
            $table->longText('description')->nullable();
            $table->integer('stock');
            $table->boolean('unlimited_stock')->default(false);
            $table->integer('max_quantity');
            $table->json('variant_attributes');
            $table->integer('product_id')->index();
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
        Schema::dropIfExists(config('commerce.database_prefix').'variants');
    }
}
