<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoLimitsAndAnnualLimitToRegions extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('regions', function ($table) {
            $table->boolean('no_limits')->after('abbreviation')->nullable();
            $table->decimal('annual_incentive_limit', 18, 2)->after('no_limits')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('no_limits');
            $table->dropColumn('annual_incentive_limit');
        });
    }

}
