<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers, ThrottlesLogins;

    /**
     * Redirect setelah login
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Batas percobaan login gagal
     *
     * @var int
     */
    protected $maxAttempts = 5; // 5 kali percobaan

    /**
     * Waktu lockout (menit) setelah gagal login
     *
     * @var int
     */
    protected $decayMinutes = 2; // 2 menit

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        // Paksa HTTPS
        $this->middleware(function ($request, $next) {
            if (!$request->isSecure() && app()->environment('production')) {
                return redirect()->secure($request->getRequestUri());
            }
            return $next($request);
        });
    }

    /**
     * Validasi input login
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // minimal 1 huruf besar
                'regex:/[a-z]/',      // minimal 1 huruf kecil
                'regex:/[0-9]/',      // minimal 1 angka
                'regex:/[@$!%*?&#]/', // minimal 1 simbol
            ],
        ]);
    }

    /**
     * Regenerasi session setelah login
     */
    protected function authenticated(Request $request, $user)
    {
        $request->session()->regenerate();

        // Simpan waktu login untuk session timeout
        Session::put('lastActivityTime', time());
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('status', 'You have been logged out.');
    }

    /**
     * Redirect setelah login berhasil
     */
    public function redirectTo()
    {
        return Session::get('backUrl') ? Session::get('backUrl') : $this->redirectTo;
    }

    /**
     * Username field (email / username)
     */
    public function username()
    {
        return 'email';
    }
}
