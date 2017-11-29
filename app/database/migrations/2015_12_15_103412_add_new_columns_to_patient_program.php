<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToPatientProgram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program', function ($table) {
            $table->timestamp('date_added')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->string('birth_weight')->nullable();
            $table->string('pediatrician_id')->nullable();
            $table->boolean('discontinue')->nullable();
            $table->smallInteger('discontinue_reason_id')->nullable();
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
        Schema::table('patient_program', function (Blueprint $table) {
            $table->dropColumn('date_added');
            $table->dropColumn('due_date');
            $table->dropColumn('delivery_date');
            $table->dropColumn('birth_weight');
            $table->dropColumn('pediatrician_id');
            $table->dropColumn('discontinue');
            $table->dropColumn('discontinue_reason_id');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }

}
