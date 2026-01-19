<?php

namespace FrancescoPrisco\NovaMongoDB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InterceptNovaNotifications
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Intercept Nova notification API calls to prevent SQL queries on MongoDB
        if (str_contains($request->path(), 'nova-notifications')) {
            // Return appropriate empty response based on HTTP method
            if ($request->isMethod('GET')) {
                return response()->json([
                    'data' => [],
                    'links' => [
                        'first' => $request->url() . '?page=1',
                        'last' => $request->url() . '?page=1',
                        'prev' => null,
                        'next' => null,
                    ],
                    'meta' => [
                        'current_page' => 1,
                        'from' => null,
                        'last_page' => 1,
                        'links' => [
                            [
                                'url' => null,
                                'label' => '&laquo; Previous',
                                'active' => false,
                            ],
                            [
                                'url' => $request->url() . '?page=1',
                                'label' => '1',
                                'active' => true,
                            ],
                            [
                                'url' => null,
                                'label' => 'Next &raquo;',
                                'active' => false,
                            ],
                        ],
                        'path' => $request->url(),
                        'per_page' => 15,
                        'to' => null,
                        'total' => 0,
                        'unread_count' => 0,
                    ]
                ], 200);
            }
            
            // For POST/PATCH/DELETE requests, return success
            return response()->json(['success' => true], 200);
        }
        
        return $next($request);
    }
}
