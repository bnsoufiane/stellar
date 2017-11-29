<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnIncentiveFlagToPatientProgramVisitTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_program_visits', function(Blueprint $table)
		{
			$table->tinyInteger('is_add_on_incentive')->after('julian_date')->unsigned()->nullable()->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patient_program_visits', function(Blueprint $table)
		{
			//
		});
	}

}
