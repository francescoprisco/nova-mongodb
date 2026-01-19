<?php

namespace FrancescoPrisco\NovaMongoDB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableNovaNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Intercept Nova notification requests and return empty response
        if ($request->is('nova-api/notifications*')) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'per_page' => 15,
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => null,
                    'to' => null,
                ]
            ]);
        }

        return $next($request);
    }
}
