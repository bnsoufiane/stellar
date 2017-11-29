<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEligibilityFieldsToPregnanciesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pregnancies', function ($table) {
            $table->boolean('eligible_for_gift_incentive')->after('enrolled_by')->nullable()->default(0);
            $table->timestamp('eligible_date')->after('eligible_for_gift_incentive')->nullable();
            $table->text('eligibility_notes')->after('member_completed_required_visit_dates')->nullable();
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
            $table->dropColumn('eligible_for_gift_incentive');
            $table->dropColumn('eligible_date');
            $table->dropColumn('eligibility_notes');
        });
    }

}
