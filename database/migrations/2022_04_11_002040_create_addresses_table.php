<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->string('zip_code');
            $table->string('street');
            $table->integer('house_number');
            $table->string('neighborhood');
            $table->string('complement')->nullable();
            $table->string('observation')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('state_id')->constrained('states');
            $table->foreignId('city_id')->constrained('cities');

            $table->timestamps();
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
        Schema::dropIfExists('addresses');
    }
}
