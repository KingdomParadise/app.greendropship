<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('refund_reason', 150);
            $table->string('refund_notes', 150);
            $table->double('request_shipping_cost', 8, 2);
            $table->double('request_amount', 8, 2);
            $table->double('refunded_shipping_cost', 8, 2)->nullable();
            $table->double('refunded_amount', 8, 2)->nullable();
            $table->timestamps();

            // define references
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_refunds');
    }
}
