<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherBackgroundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weather_backgrounds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country', 2);
            $table->string('province', 2);
            $table->string('city', 5);
            $table->string('weather', 15);
            $table->string('period', 10);
            $table->string('support', 3);
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
        Schema::dropIfExists('weather_backgrounds');
    }
}
