<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('shopwire.table_prefix') . 'shipping_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('active')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('rate', 8, 2);
            $table->boolean('vat_included')->default(1);
            $table->decimal('amount_from', 15, 5)->nullable();
            $table->decimal('amount_to', 15, 5)->nullable();
            $table->decimal('weight_from', 15, 5)->nullable();
            $table->decimal('weight_to', 15, 5)->nullable();
            $table->text('countries')->nullable();
            $table->text('countries_except')->nullable();
            $table->integer('sort')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['active', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('shopwire.table_prefix') . 'shipping_rates');
    }
}
