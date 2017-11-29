<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ManualOutreaches extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_outreaches', function ($table) {
            $table->integer('patient_id');
            $table->integer('program_id');
            $table->timestamp('outreach_date')->nullable();
            $table->integer('outreach_code')->nullable();;
            $table->text('outreach_notes')->nullable();
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
        Schema::drop('manual_outreaches');
    }

}
