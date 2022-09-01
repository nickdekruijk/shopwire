<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->boolean('active')->default(1);
            $table->string('name');
            $table->string('head')->nullable();
            $table->string('html_title', 65)->nullable();
            $table->string('slug', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('video_id', 100)->nullable();
            $table->integer('delivery_time')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->foreignId('vat_id')->nullable()->constrained(config('shopwire.table_prefix') . 'vats')->onDelete('cascade');
            $table->decimal('stock', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('images')->nullable();
            $table->text('thumbnail')->nullable();
            $table->longText('body')->nullable();
            $table->unsignedBigInteger('sort')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'name', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
