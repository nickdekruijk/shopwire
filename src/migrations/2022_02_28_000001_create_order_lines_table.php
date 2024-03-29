<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('shopwire.table_prefix') . 'order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('order_id')->constrained(config('shopwire.table_prefix') . 'orders')->onDelete('cascade');

            $product = config('shopwire.product_model');
            $table->foreignId('product_id')->nullable();

            $product_option = config('shopwire.product_option_model');
            if ($product_option) {
                $table->foreignId('product_option_id')->nullable()->constrained((new $product_option)->getTable());
            } else {
                $table->foreignId('product_option_id')->nullable();
            }

            $table->string('title');
            $table->decimal('quantity', 15, 5);
            $table->decimal('price', 10, 2);
            $table->decimal('weight', 15, 5)->nullable();
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->boolean('vat_included')->default(0);
            $table->unsignedBigInteger('sort')->nullable()->index();
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
        Schema::dropIfExists(config('shopwire.table_prefix') . 'order_lines');
    }
}
