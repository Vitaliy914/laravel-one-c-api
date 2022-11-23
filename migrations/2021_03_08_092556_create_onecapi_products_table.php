<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnecapiProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onecapi_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->index();
            $table->string('group_sku')->nullable()->index();
            $table->string('name');
            $table->string('art')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('residue')->nullable()->default(0);
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
        Schema::dropIfExists('onecapi_products');
    }
}
