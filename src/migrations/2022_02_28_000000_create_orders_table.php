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
        Schema::create(config('shopwire.table_prefix') . 'orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('paid')->index()->default(0);
            $table->bigInteger('status')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->json('customer')->nullable();
            $table->json('products')->nullable();
            $table->text('html')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
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
        Schema::dropIfExists(config('shopwire.table_prefix') . 'orders');
    }
}
