<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * Fails closed: with no ADMIN_ACCESS_TOKEN configured there is no admin URL
     * at all, rather than a literal committed to the repository standing in for
     * the secret.
     */
    private function validToken(string $token): bool
    {
        $expected = config('app.admin_access_token');

        return is_string($expected) && $expected !== '' && hash_equals($expected, $token);
    }

    public function showLogin(string $hash)
    {
        if (!$this->validToken($hash)) {
            abort(404);
        }

        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login', ['hash' => $hash]);
    }

    public function login(Request $request, string $hash)
    {
        if (!$this->validToken($hash)) {
            abort(404);
        }

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access denied. Admin account required.']);
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is inactive.']);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
