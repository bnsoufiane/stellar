<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProgramInstanceIdToManualOutreaches extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_outreaches', function ($table) {
            $table->integer('program_instance_id')->after('program_id')->unsigned()->nullable()->default(0);
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
            $table->dropColumn('program_instance_id');
        });
    }

}
