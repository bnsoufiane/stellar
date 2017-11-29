<?php

use Cartalyst\Sentry\Users\Eloquent\User;

// Adds a default system user "curotec"
class DefaultUserSeeder extends Seeder {
    public function run() {
        // System administrator
        $user = Sentry::createUser(array(
            'username' => 'curotec',
            'password' => 'curotec',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'activated' => true,
            'region_id' => null
        ));

        $user->addGroup(Sentry::findGroupByName('System Administrator'));

        $user = Sentry::createUser(array(
            'username' => 'soufiane',
            'password' => 'soufiane',
            'first_name' => 'Soufiane',
            'last_name' => 'Ben Lamalem',
            'activated' => true,
            'region_id' => null
        ));

        $user->addGroup(Sentry::findGroupByName('System Administrator'));
    }
}
