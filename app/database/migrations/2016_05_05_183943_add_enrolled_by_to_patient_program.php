<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnrolledByToPatientProgram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program', function ($table) {
            $table->tinyInteger('enrolled_by')->after('gestational_age')->unsigned()->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_program', function (Blueprint $table) {
            $table->dropColumn('enrolled_by');
        });
    }

}
