<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductMapAttributesTable extends Migration
{
  
    public function up()
    {
        Schema::create('product_map_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('product_map_attributes');
    }
}
