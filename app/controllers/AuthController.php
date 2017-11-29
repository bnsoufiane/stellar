<?php


class AuthController extends BaseController {
	protected $layout = 'layouts.sign-in';

	public function showAuthForm() {
		$this->layout->content = View::make('auth-form')
            ->with('error', Session::get('error'));
	}

	public function postAuthForm() {
		$username = Input::get('username');
		$password = Input::get('password');

		$credentials = array(
			'username' => $username,
			'password' => $password
		);

        try {
            Sentry::authenticate($credentials);
            return Redirect::route('index');
        } catch (\Exception $e) {
            return Redirect::route('sign-in')
                ->withInput()
                ->with('error', 'Wrong username or password.');
        }
	}

    public function signOut() {
        Sentry::logout();
        return Redirect::route('sign-in');
    }

    public function checkSession() {

        $bag = Session::getMetadataBag();
        $max = Config::get('session.lifetime') * 60;

        if ($bag && $max < (time() - $bag->getLastUsed())) {
            Sentry::logout();
            return "inactive";
        }else{
            return "active";
        }
    }

}
