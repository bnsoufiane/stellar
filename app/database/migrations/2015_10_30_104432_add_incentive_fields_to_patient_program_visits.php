<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncentiveFieldsToPatientProgramVisits extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->string('incentive')->after('scheduled_visit_date')->nullable();
            $table->string('gift_card_serial')->after('incentive')->nullable();
            $table->timestamp('incentive_date_sent')->after('gift_card_serial')->nullable();
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
            $table->dropColumn('incentive');
            $table->dropColumn('gift_card_serial');
            $table->dropColumn('incentive_date_sent');
        });
    }

}
