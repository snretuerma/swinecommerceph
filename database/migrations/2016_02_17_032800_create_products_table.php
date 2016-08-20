<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('breeder_id')->unsigned();
            $table->integer('farm_from_id')->unsigned();
            $table->integer('primary_img_id')->unsigned();
            $table->string('name');
            $table->string('type');
            $table->integer('age');
            $table->integer('breed_id');
            $table->float('price')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('adg')->nullable();
            $table->float('fcr')->nullable();
            $table->float('backfat_thickness')->nullable();
            $table->text('other_details')->nullable();
            $table->enum('status',
                ['hidden', 'displayed', 'requested', 'reserved', 'paid', 'on_delivery', 'sold']
                )->default('hidden');
            $table->integer('customer_id')->unsigned();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }
}
