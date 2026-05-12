<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\RoleRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect(RoleRedirectService::getDashboardRoute());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.inactive'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(RoleRedirectService::getDashboardRoute());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }
}
