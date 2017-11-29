<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirstTrimesters extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('first_trimesters', function ($table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned();
            $table->integer('program_id')->unsigned();
            $table->integer('pregnancy_id')->unsigned()->nullable()->default(0);
            $table->timestamp('sign_up')->nullable();
            $table->boolean('open')->nullable()->default(0);
            $table->tinyInteger('enrolled_by')->unsigned()->nullable()->default(0);
            $table->timestamp('date_added')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('first_trimester_start')->nullable();
            $table->timestamp('first_trimester_end')->nullable();
            $table->boolean('discontinue')->nullable();
            $table->smallInteger('discontinue_reason_id')->nullable();
            $table->date('discontinue_date')->nullable();
            $table->tinyInteger('how_did_you_hear')->unsigned()->nullable()->default(0);
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
        Schema::drop('first_trimesters');
    }

}
