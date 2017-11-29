<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePpImportSummaryDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pp_import_summary_data', function(Blueprint $table)
		{

			$table->engine = 'MyISAM';
			$table->integer('region_id');
			$table->integer('id')->unsigned();
			$table->string('username', 100);
			$table->string('first_name', 100);
			$table->string('last_name', 100);
			$table->string('actual_visit_date', 100);
			$table->string('doctor_id', 100);
			$table->string('incentive_type', 100);
			$table->integer('incentive_value');
			$table->string('gift_card_serial', 100);
			$table->string('incentive_date_sent', 100);
			$table->text('visit_notes', 65535);
			$table->string('reason', 250);
			$table->integer('user_id');			
			$table->primary(array('region_id', 'id'));

		
		});

			Schema::table('pp_import_summary_data', function(Blueprint $table)
		{
		
          	\DB::statement('ALTER TABLE pp_import_summary_data MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');
             
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pp_import_summary_data');
	}

}
