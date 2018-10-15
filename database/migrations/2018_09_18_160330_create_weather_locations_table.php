<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weather_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country', 2);
            $table->string('province', 2);
            $table->string('city', 30);
            $table->string('selection', 10);
            $table->timestamps();

            $table->unique(['country', 'province', 'city']);
        });

	    Schema::table('weather_backgrounds', function (Blueprint $table) {
			$table->dropColumn(['country', 'province', 'city']);
		    $table->integer('location');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weather_locations');
    }
}
