<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMedicaidIdAndMiddleInitialToUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('medicaid_id')->after('username')->nullable();
            $table->string('middle_initial')->after('last_name')->nullable();
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
            $table->dropColumn('medicaid_id');
            $table->dropColumn('middle_initial');
        });
    }

}
