<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsDynamicBackgrounds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creating the news dynamic backgrounds table
        Schema::create('news_backgrounds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category');
            $table->string('support', 3);
            $table->string('locale', 5);
            $table->timestamps();
        });

        // Adding foreign keys
        Schema::table('news_backgrounds' , function (Blueprint $table) {
            $table->foreign('category', 'fk_background_category')
                ->references('id')
                ->on('news_categories')
                ->onUpdate('cascade')
                ->onDelete('no action');

            $table->unique(['category', 'support', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_backgrounds');
    }
}
