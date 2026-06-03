<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $params = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => config('services.google.redirect'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/auth?' . $params);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/')->with('error', 'Google login cancelled.');
        }

        try {
            $tokenResponse = Http::post('https://oauth2.googleapis.com/token', [
                'code'          => $request->get('code'),
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => config('services.google.redirect'),
                'grant_type'    => 'authorization_code',
            ]);

            $accessToken = $tokenResponse->json('access_token');

            $userInfo = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->json();

            $user = User::firstOrCreate(
                ['google_id' => $userInfo['sub']],
                [
                    'name'              => $userInfo['name'],
                    'email'             => $userInfo['email'],
                    'avatar'            => $userInfo['picture'] ?? null,
                    'email_verified_at' => now(),
                    'password'          => null,
                ]
            );

            if (!$user->is_active) {
                return redirect('/')->with('error', 'Your account is inactive.');
            }

            Auth::login($user, true);

            return redirect()->intended('/');
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Google login failed. Please try again.');
        }
    }
}
