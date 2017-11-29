<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncentiveValueToPatientProgramVisits extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->renameColumn('incentive', 'incentive_type');
            $table->decimal('incentive_value', 18, 2)->after('incentive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->dropColumn('incentive_value');
            $table->renameColumn('incentive_type', 'incentive');
        });
    }

}
