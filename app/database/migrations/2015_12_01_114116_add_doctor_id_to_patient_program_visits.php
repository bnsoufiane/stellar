<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDoctorIdToPatientProgramVisits extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->integer('doctor_id')->after('incentive_date_sent')->nullable();
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
            $table->dropColumn('doctor_id');
        });
    }

}
