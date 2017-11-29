<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHowDidYouHearAndPrimaryInsuranceToPregnancies extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnancies', function ($table) {
            $table->tinyInteger('how_did_you_hear')->after('gestational_age')->unsigned()->nullable()->default(0);
            $table->boolean('primary_insurance')->after('how_did_you_hear')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pregnancies', function (Blueprint $table) {
            $table->dropColumn('how_did_you_hear');
            $table->dropColumn('primary_insurance');
        });
    }

}
