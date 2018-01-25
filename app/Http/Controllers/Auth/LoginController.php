<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use App\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = $this->findOrCreateUser(
            Socialite::driver('github')->user()
        );
        Auth::login($user);
        return redirect()->intended($this->redirectTo);
    }

    public function findOrCreateUser($githubUser)
    {
        $user = User::firstOrNew(['github_id' => $githubUser->getId()]);
        if($user->exists)   return $user;

        $user->fill([
            'name' => $githubUser->getName(),
            'email' => $githubUser->getEmail(),
            'avatar' => $githubUser->getAvatar(),
            'password' => bcrypt(env('DEFAULT_PASSWORD')),
        ])->save();
        return $user;
    }
}
