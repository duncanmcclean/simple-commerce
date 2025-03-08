<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_line_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('order_id')->index();
            $table->string('product');
            $table->string('variant')->nullable();
            $table->integer('quantity');
            $table->integer('unit_price');
            $table->integer('sub_total');
            $table->integer('tax_total');
            $table->integer('total');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_line_items');
    }
};
