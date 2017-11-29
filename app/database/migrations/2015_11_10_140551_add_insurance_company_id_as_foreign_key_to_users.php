<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInsuranceCompanyIdAsForeignKeyToUsers extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE users MODIFY COLUMN insurance_company_id int(10) unsigned');

        Schema::table('users', function ($table) {
            $table->foreign('insurance_company_id')->references('id')->on('insurance_companies');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE users MODIFY COLUMN insurance_company_id int(11)');
    }

}
