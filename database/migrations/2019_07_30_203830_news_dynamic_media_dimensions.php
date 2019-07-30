<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsDynamicMediaDimensions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Adding new columns to the news_records tables
        Schema::table('news_records', function (Blueprint
                                                $table) {
            $table->integer('media_width')->after('media')->nullable();
            $table->integer('media_height')->after('media_width')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the media dimensions columns
        Schema::table('news_records', function (Blueprint $table) {
            $table->dropColumn(['media_width', 'media_height']);
        });

    }
}
