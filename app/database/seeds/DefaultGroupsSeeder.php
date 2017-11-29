<?php

class DefaultGroupsSeeder extends Seeder {

    public function run() {
        // System administrators
        Sentry::createGroup(array(
            'name' => 'System Administrator',
            'permissions' => array(
                'admin.*' => 1,
            )
        ));

        // Clients
        Sentry::createGroup(array(
            'name' => 'Client',
            'permissions' => array(
                'admin.*' => 1,
            )
        ));

        // Patients
        Sentry::createGroup(array(
            'name' => 'Patient',
            'permissions' => array(
                'admin.*' => 1,
            )
        ));
    }

}
