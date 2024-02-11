<?php

use DuncanMcClean\SimpleCommerce\Orders\OrderModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class CreateStatusLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_log', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index();
            $table->string('status')->index();
            $table->timestamp('timestamp')->index();
            $table->json('data')->nullable();
        });

        OrderModel::query()->chunkById(100, function ($orders) {
            $orders->each(function (OrderModel $order) {
                $statusLog = Arr::get($order->data, 'status_log', []);

                foreach ($statusLog as $statusLogEvent) {
                    $order->statusLog()->createOrFirst([
                        'status' => $statusLogEvent['status'],
                        'timestamp' => $statusLogEvent['timestamp'],
                        'data' => $statusLogEvent['data'],
                    ]);
                }

                $order->updateQuietly([
                    'data' => collect($order->data)
                        ->reject(fn ($value, $key) => $key === 'status_log')
                        ->all(),
                ]);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_log');
    }
}
