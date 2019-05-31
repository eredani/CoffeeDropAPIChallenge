<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HistoryCashBack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oldcashback', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('ip')->index();
            $table->string('agent');
            $table->json('postData');
            $table->float('cashBack');
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
        Schema::dropIfExists('oldcashback');
    }
}
