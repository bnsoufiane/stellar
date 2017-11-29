<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientProgramVisitsA1cMetrics extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->string('urine')->after('doctor_id')->nullable();
            $table->string('blood')->after('urine')->nullable();
            $table->string('eye')->after('blood')->nullable();
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
            $table->dropColumn('urine');
            $table->dropColumn('blood');
            $table->dropColumn('eye');
        });
    }

}
