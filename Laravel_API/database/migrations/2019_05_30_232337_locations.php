<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Locations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function ( Blueprint $table ) {
            $table->increments('id');
            $table->string('postCode')->unique();
            $table->decimal('lat', 11, 8 );
            $table->decimal('lng', 11, 8 );
            $table->json('timeTable');
            $table->timestamps();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations' );
    }
}
