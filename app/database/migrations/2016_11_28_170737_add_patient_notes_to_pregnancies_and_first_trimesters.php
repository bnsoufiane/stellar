<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientNotesToPregnanciesAndFirstTrimesters extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnancies', function ($table) {
            $table->text('patient_notes')->after('open')->nullable();
        });

        Schema::table('first_trimesters', function ($table) {
            $table->text('patient_notes')->after('open')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn('patient_notes');
        });

        Schema::table('first_trimesters', function (Blueprint $table) {
            $table->dropColumn('patient_notes');
        });
    }

}
