<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherBackgroundsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'weather_backgrounds';

    /**
     * Run the migrations.
     * @table weather_backgrounds
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('weather', 15);
            $table->string('period', 10);
            $table->string('support', 3);
            $table->unsignedInteger('location');

            $table->index(["location"], 'fk_weather_backgrounds_weather_locations_id1_idx');
            $table->nullableTimestamps();


            $table->foreign('location', 'fk_weather_backgrounds_weather_locations_id1_idx')
                ->references('id')->on('weather_locations')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
