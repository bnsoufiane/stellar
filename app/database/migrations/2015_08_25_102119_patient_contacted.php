<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientContacted extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_contacted', function ($table) {
            $table->integer('patient_id');
            $table->integer('program_id');
            $table->integer('contact_tool');
            $table->integer('phone_id')->nullable();
            $table->integer('status');
            $table->integer('content_id');
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
        Schema::drop('patient_contacted');
    }

}
