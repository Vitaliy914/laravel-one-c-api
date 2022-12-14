<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnecapiPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onecapi_prices', function (Blueprint $table) {
            $table->id();
            $table->string('product_sku')->index();
            $table->string('type_sku')->index();
            $table->string('view')->nullable();
            $table->string('price_per_unit')->nullable();
            $table->string('currency')->nullable();
            $table->string('unit')->nullable();
            $table->string('ratio')->nullable();
            $table->string('discount')->nullable();
            $table->string('price_with_discount')->nullable();
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
        Schema::dropIfExists('onecapi_prices');
    }
}
