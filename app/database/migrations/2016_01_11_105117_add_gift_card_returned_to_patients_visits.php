<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGiftCardReturnedToPatientsVisits extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program_visits', function ($table) {
            $table->boolean('gift_card_returned')->after('manual_outreach_notes')->nullable()->default(0);
            $table->string('gift_card_returned_notes')->after('gift_card_returned')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_program_visits', function (Blueprint $table) {
            $table->dropColumn('gift_card_returned');
            $table->dropColumn('gift_card_returned_notes');
        });
    }

}
