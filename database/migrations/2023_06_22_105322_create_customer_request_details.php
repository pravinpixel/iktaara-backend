<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerRequestDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_request_details', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('mobile_no')->nullable();
            $table->string('location');
            $table->string('pincode');
            $table->enum('customer_categories', ['learn', 'play', 'perform', 'connect', 'upgrade']);
            $table->enum('customer_designation', ['teacher', 'student', 'publisher', 'player'])->nullable();
            $table->text('desc')->nullable();
            $table->boolean('is_agree')->nullable();
            
            $table->softDeletes();   
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
        Schema::dropIfExists('customer_request_details');
    }
}
