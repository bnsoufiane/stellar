<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScheduledVisitDateToPatientProgram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->timestamp('scheduled_visit_date')->after('actual_visit_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_program_visits', function (Blueprint $table) {
            $table->dropColumn('scheduled_visit_date');
        });
    }

}
