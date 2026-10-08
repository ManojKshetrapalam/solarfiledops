<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->password_change_required) {
                $allowedRoutes = [
                    'auth.change-password',
                    'auth.change-password.update',
                    'logout',
                ];

                if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'error' => 'Password change required before proceeding.',
                            'redirect' => route('auth.change-password'),
                        ], 403);
                    }

                    return redirect()->route('auth.change-password')
                        ->with('warning', 'For security, you must change your temporary password before continuing.');
                }
            }
        }

        return $next($request);
    }
}
