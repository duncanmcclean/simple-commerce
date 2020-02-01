<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('commerce.database_prefix').'products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uid')->unique();
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->integer('product_category_id')->index()->nullable();
            $table->boolean('is_enabled')->default(false);
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
        Schema::dropIfExists(config('commerce.database_prefix').'products');
    }
}
