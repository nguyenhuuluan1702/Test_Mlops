<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }
        
        if (Auth::user()->role->RoleCode !== 'admin') {
            // If user is logged in but not admin, show role mismatch page
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied. Admin privileges required.',
                    'role_mismatch' => true,
                    'current_role' => Auth::user()->role->RoleCode,
                    'required_role' => 'admin'
                ], 403);
            }
            
            return response()->view('errors.role-mismatch', [
                'current_role' => Auth::user()->role->RoleCode,
                'required_role' => 'admin',
                'user_name' => Auth::user()->FullName
            ], 403);
        }

        return $next($request);
    }
}
