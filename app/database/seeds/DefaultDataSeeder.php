<?php

use Cartalyst\Sentry\Users\Eloquent\User;

// Adds a default system user "curotec"
class DefaultDataSeeder extends Seeder
{
    public function run()
    {
        //phones pool
        $phone = new Phone(array('phone_number' => '+13346895468'));
        $phone->save();
        $phone = new Phone(array('phone_number' => '+212662152302'));
        $phone->save();
        $phone = new Phone(array('phone_number' => '+212624830016'));
        $phone->save();

        // Creating Insurance Companies
        $insurance_company_1 = new InsuranceCompany(array('name' => "Amerihealth Caritas"));
        $insurance_company_2 = new InsuranceCompany(array('name' => "Penn Mutual"));
        $insurance_company_1->save();
        $insurance_company_2->save();

        // Creating Regions
        $region = new Region(array('name' => "I Am Healthy - District of Colombia (DC Region)"));
        $region->insurance_company()->associate($insurance_company_1);
        $region->save();

        $region = new Region(array('name' => "Keystone First (SE region PA)"));
        $region->insurance_company()->associate($insurance_company_1);
        $region->save();

        $region = new Region(array('name' => "Northeast (NE Region PA)"));
        $region->insurance_company()->associate($insurance_company_1);
        $region->save();

        $region = new Region(array('name' => "PA (NW Region PA)"));
        $region->insurance_company()->associate($insurance_company_1);
        $region->save();

        $region = new Region(array('name' => "New York"));
        $region->insurance_company()->associate($insurance_company_2);
        $region->save();

        $region = new Region(array('name' => "Pennsylvania"));
        $region->insurance_company()->associate($insurance_company_2);
        $region->save();

        $region = new Region(array('name' => "Virginia"));
        $region->insurance_company()->associate($insurance_company_2);
        $region->save();
    }
}
