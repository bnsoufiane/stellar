<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimaryInsuranceToPatientProgram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program', function ($table) {
            $table->boolean('primary_insurance')->after('how_did_you_hear')->nullable()->default(0);
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
            $table->dropColumn('primary_insurance');
        });
    }

}
