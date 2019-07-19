<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewsDynamic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // With the arrival of the news dynamics and its own records, we rename the weather records 'records' table to 'weather_records' for consistency
        Schema::rename('records', 'weather_records');

        // We create the news dynamics necessary tables
        Schema::create('news_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 45);
            $table->string('locale', 5);
            $table->timestamps();
        });

        Schema::create('news_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 32);
            $table->string('label', 64);
            $table->string('locale', 5);
            $table->integer('category');
            $table->timestamps();
        });

        Schema::create('news_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cp_id', 64);
            $table->integer('subject');
            $table->string('locale', 5);
            $table->string('headline', 512);
            $table->timestamp('date');
            $table->string('media', 64)->nullable();
            $table->timestamps();
        });

        Schema::table('news_records', function(Blueprint $table) {
           $table->unique('cp_id');
        });

        Schema::table('news_subjects' , function (Blueprint $table) {
            $table->foreign('category', 'fk_subject_category')
                ->references('id')
                ->on('news_categories')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });

        Schema::table('news_records' , function (Blueprint $table) {
            $table->foreign('subject', 'fk_record_subject')
                ->references('id')
                ->on('news_subjects')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

//        DB::table('dynamics')->insert(['slug' => 'news', 'name' => 'News']);

        DB::table('news_categories')->insert([
            ['name' => 'National News', 'locale' => 'en'],
            ['name' => 'International News', 'locale' => 'en'],
            ['name' => 'Sports', 'locale' => 'en'],
            ['name' => 'Business', 'locale' => 'en'],
            ['name' => 'Entertainment', 'locale' => 'en'],
            ['name' => 'Variety', 'locale' => 'en'],
            ['name' => 'News', 'locale' => 'fr'],
            ['name' => 'Variety', 'locale' => 'fr'],
            ['name' => 'Sports', 'locale' => 'fr'],
        ]);

        DB::table('news_subjects')->insert([
            ['slug' => 'business', 'label' => 'Business', 'locale' => 'en', 'category' => '4'],
            ['slug' => 'consumerTech', 'label' => 'Technology', 'locale' => 'en', 'category' => '6'],
            ['slug' => 'entertainment', 'label' => 'Entertainment', 'locale' => 'en', 'category' => '5'],
            ['slug' => 'environment', 'label' => 'Environment', 'locale' => 'en', 'category' => '6'],
            ['slug' => 'FrBusiness', 'label' => 'Business', 'locale' => 'fr', 'category' => '7'],
            ['slug' => 'FrEnvironment', 'label' => 'Environment', 'locale' => 'fr', 'category' => '8'],
            ['slug' => 'FrHealth', 'label' => 'Santé', 'locale' => 'fr', 'category' => '8'],
            ['slug' => 'FrMedia', 'label' => 'Médias', 'locale' => 'fr', 'category' => '9'],
            ['slug' => 'FrNational', 'label' => 'National', 'locale' => 'fr', 'category' => '7'],
            ['slug' => 'FrSports', 'label' => 'Sports', 'locale' => 'fr', 'category' => '9'],
            ['slug' => 'FrWorld', 'label' => 'International', 'locale' => 'fr', 'category' => '7'],
            ['slug' => 'health', 'label' => 'Health', 'locale' => 'en', 'category' => '6'],
            ['slug' => 'hockey', 'label' => 'Hockey', 'locale' => 'en', 'category' => '3'],
            ['slug' => 'national', 'label' => 'National', 'locale' => 'en', 'category' => '1'],
            ['slug' => 'oddities', 'label' => 'Oddities', 'locale' => 'en', 'category' => '5'],
            ['slug' => 'sports', 'label' => 'Sports', 'locale' => 'en', 'category' => '3'],
            ['slug' => 'world', 'label' => 'World', 'locale' => 'en', 'category' => '2'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::rename('weather_records', 'records');

        Schema::dropIfExists('news_records');
        Schema::dropIfExists('news_subjects');
        Schema::dropIfExists('news_categories');
    }
}
