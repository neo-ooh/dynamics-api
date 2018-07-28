<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name', 25);
            $table->char('key', 64);
            $table->timestamps();
        });

	    Schema::create('authorizations', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('key')->unsigned();
		    $table->integer('dynamic')->unsigned();
		    $table->timestamps();
	    });

	    Schema::table('authorizations' , function (Blueprint $table) {
		    $table->foreign('key', 'fk_auth_key')
			    ->references('id')
			    ->on('keys')
			    ->onDelete('cascade');

		    $table->foreign('dynamic', 'fk_auth_user')
			    ->references('id')
			    ->on('dynamics')
			    ->onDelete('cascade');

		    $table->unique(['key', 'dynamic']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
}
