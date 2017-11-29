<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Programs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->text('notes')->nullable();
            $table->text('sms_content')->nullable();
            $table->string('call_text')->nullable();
            $table->string('call_mp3')->nullable();
            $table->text('email_content')->nullable();
            $table->integer('contact_frequency_times');
            $table->integer('contact_frequency_period');
            $table->integer('visit_requirement_times');
            $table->integer('visit_requirement_period');
            $table->integer('region_id');
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
        Schema::drop('programs');
    }

}
