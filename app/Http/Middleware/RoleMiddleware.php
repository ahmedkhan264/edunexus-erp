<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role->name ?? null;

        // If no role parameter defined, allow all authenticated users
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user's role matches any of the allowed roles
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Role not authorized – redirect based on user role or default
        switch ($userRole) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Teacher':
                return redirect()->route('teacher.dashboard');
            case 'Student':
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
}
