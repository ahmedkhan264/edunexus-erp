<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Only redirect if trying to access guest routes (login, register)
                $guestRoutes = ['login', 'register'];
                $currentRoute = $request->route()->getName();
                
                // Check if current route is a guest route or the root route
                if (in_array($currentRoute, $guestRoutes) || $request->path() === '/') {
                    $user = Auth::user();
                    
                    // Redirect based on user role
                    switch ($user->role->slug) {
                        case 'super_admin':
                        case 'admin':
                            return redirect('/admin/dashboard');
                        case 'principal':
                            return redirect('/principal/dashboard');
                        case 'teacher':
                            return redirect('/teacher/dashboard');
                        case 'student':
                            return redirect('/student/dashboard');
                        case 'parent':
                            return redirect('/parent/dashboard');
                        case 'accountant':
                            return redirect('/accountant/dashboard');
                        case 'hr_manager':
                            return redirect('/hr/dashboard');
                        case 'librarian':
                            return redirect('/library/dashboard');
                        case 'timetable_coordinator':
                            return redirect('/timetable/dashboard');
                        default:
                            return redirect('/dashboard');
                    }
                }
                
                // If already on a valid dashboard route, continue
                return $next($request);
            }
        }

        return $next($request);
    }
}
