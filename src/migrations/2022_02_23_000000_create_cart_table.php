<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('shopwire.table_prefix') . 'carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id')->index();
            $table->bigInteger('user_id')->unsigned()->nullable()->index();
            $table->string('country_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('shipping_rate_id')->nullable()->constrained(config('shopwire.table_prefix') . 'shipping_rates');
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
        Schema::dropIfExists(config('shopwire.table_prefix') . 'carts');
    }
}
