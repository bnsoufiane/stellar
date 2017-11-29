<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePregnancies extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pregnancies', function ($table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned();
            $table->integer('program_id')->unsigned();
            $table->boolean('open')->nullable()->default(0);
            $table->timestamp('date_added')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->string('birth_weight')->nullable();
            $table->string('pediatrician_id')->nullable();
            $table->boolean('discontinue')->nullable();
            $table->smallInteger('discontinue_reason_id')->nullable();
            $table->date('discontinue_date')->nullable();
            $table->decimal('gestational_age', 10, 2)->nullable();
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
        Schema::drop('pregnancies');
    }

}
