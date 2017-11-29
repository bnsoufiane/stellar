<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoctorsFirstAndLastNameInsteadOfFullName extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors', function ($table) {
            $table->dropColumn('name');
            $table->string('first_name')->after('pcp_id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }

}
