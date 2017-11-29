<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisitDateVerifiedByAndJulianDateToPatientProgramVisits extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->integer('visit_date_verified_by')->after('created_by')->unsigned()->nullable()->default(0);
            $table->integer('julian_date')->after('visit_date_verified_by')->unsigned()->nullable()->default(0);
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
            $table->dropColumn('visit_date_verified_by');
            $table->dropColumn('julian_date');
        });
    }

}
