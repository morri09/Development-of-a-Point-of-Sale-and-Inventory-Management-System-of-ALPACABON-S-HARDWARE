<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check user's menu permissions for route access.
 * 
 * This middleware verifies that the authenticated user has permission
 * to access the requested menu item based on their menu_permissions JSON field.
 * 
 * Admins bypass this check and have access to all routes.
 * Unauthorized users are redirected to the dashboard with an error message.
 */
class MenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $menuKey  The menu key to check permission for
     */
    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        $user = $request->user();

        // If no user is authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Admins have access to all menu items
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has permission for this menu item
        if (!$user->hasMenuPermission($menuKey)) {
            // Prevent redirect loop: if already on dashboard or trying to access dashboard, show 403
            if ($menuKey === 'dashboard' || $request->routeIs('dashboard')) {
                abort(403, 'Access Denied. You do not have permission to access this page.');
            }
            
            return redirect()
                ->route('dashboard')
                ->with('error', 'Access Denied. You do not have permission to access this page.');
        }

        return $next($request);
    }
}
