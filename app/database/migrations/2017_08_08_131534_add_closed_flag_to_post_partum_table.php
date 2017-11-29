<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClosedFlagToPostPartumTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('post_partums', function(Blueprint $table)
		{
		
            $table->tinyInteger('closed')->after('postpartum_end')->unsigned()->nullable()->default(0);
             
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('post_partums', function(Blueprint $table)
		{
			$table->dropColumn('closed');
		});
	}

}
