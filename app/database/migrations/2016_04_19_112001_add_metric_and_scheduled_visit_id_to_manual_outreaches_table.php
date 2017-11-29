<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetricAndScheduledVisitIdToManualOutreachesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_outreaches', function ($table) {
            $table->tinyInteger('outreach_metric')->after('program_id')->unsigned()->nullable()->default(0);
            $table->integer('patient_program_visits_id')->after('program_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_outreaches', function (Blueprint $table) {
            $table->dropColumn('outreach_metric');
            $table->dropColumn('patient_program_visits_id');
        });
    }

}
