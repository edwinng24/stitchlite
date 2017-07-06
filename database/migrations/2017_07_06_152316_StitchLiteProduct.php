<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StitchLiteProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('stitch_lite_products', function (Blueprint $table) {
            $table->integer('stitch_lite_product_ids_id');
            $table->string('sku');
            $table->string('name');
            $table->integer('quantity');
            $table->float('price');
            $table->integer('sales_channel_id');
            $table->timestamps();
            $table->unique(['sku', 'sales_channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
