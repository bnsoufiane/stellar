<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DiscontinueTrackingWc15 extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discontinue_tracking_wc15_reasons', function ($table) {
            $table->increments('id');
            $table->string('reason')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('discontinue_tracking_wc15_reasons');
    }

}
