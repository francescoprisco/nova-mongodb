<?php

namespace FrancescoPrisco\NovaMongoDB\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class NotificationController extends Controller
{
    /**
     * Get the notifications for the authenticated user.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(NovaRequest $request)
    {
        // Return empty notifications for MongoDB
        // Nova notifications are not fully supported with MongoDB yet
        return response()->json([
            'notifications' => [],
            'unread_count' => 0,
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $notificationId
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(NovaRequest $request, $notificationId)
    {
        return response()->noContent();
    }

    /**
     * Mark all notifications as read.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead(NovaRequest $request)
    {
        return response()->noContent();
    }

    /**
     * Delete a notification.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  string  $notificationId
     * @return \Illuminate\Http\Response
     */
    public function destroy(NovaRequest $request, $notificationId)
    {
        return response()->noContent();
    }
}
