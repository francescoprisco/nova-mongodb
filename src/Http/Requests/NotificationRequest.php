<?php

namespace FrancescoPrisco\NovaMongoDB\Http\Requests;

use Laravel\Nova\Http\Requests\NotificationRequest as BaseNotificationRequest;
use Illuminate\Support\Collection;

class NotificationRequest extends BaseNotificationRequest
{
    /**
     * Get the notifications for the authenticated user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function notifications(): Collection
    {
        // Return empty collection instead of querying SQL database
        return collect([]);
    }

    /**
     * Get the unread notifications count for the authenticated user.
     *
     * @return int
     */
    public function unreadCount(): int
    {
        return 0;
    }
}
