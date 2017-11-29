<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostPartums extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_partums', function ($table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned();
            $table->integer('program_id')->unsigned();
            $table->integer('pregnancy_id')->unsigned();
            $table->text('patient_notes')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->string('birth_weight')->nullable();
            $table->decimal('gestational_age', 10, 2)->nullable();
            $table->string('pediatrician_id')->nullable();
            $table->date('postpartum_start')->nullable();
            $table->date('postpartum_end')->nullable();
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
        Schema::drop('post_partums');
    }

}
