<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('records', function (Blueprint $table) {
            $table->increments('id');  // Obs Lng
            $table->string('endpoint', 3);  // Obs Lng
            $table->string('country', 2);
            $table->string('province', 2);
            $table->string('city', 30);
            $table->string('locale', 5);
            $table->text('content');
            $table->timestamps();

	        $table->unique(['endpoint', 'country', 'province', 'city', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('records');
    }
}
