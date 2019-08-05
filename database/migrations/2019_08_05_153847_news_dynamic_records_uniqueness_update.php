<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsDynamicRecordsUniquenessUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update the unique attribute of the news records
        Schema::table('news_records', function(Blueprint $table) {
            $table->dropUnique('cp_id'); // To update
            $table->unique(['cp_id', 'subject']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert the unique attribute of the news records
        Schema::table('news_records', function(Blueprint $table) {
            $table->dropUnique(['cp_id', 'subject']);
            $table->unique('cp_id');
        });
    }
}
