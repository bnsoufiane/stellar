<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePpImportPausedDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pp_import_pause_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('filename', 250);
			$table->integer('row_imported');
			$table->integer('date_of_service')->nullable();
			$table->integer('program');
			$table->integer('region_id');
			$table->integer('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pp_import_pause_data');
	}

}
