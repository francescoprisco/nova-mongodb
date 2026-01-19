<?php

namespace FrancescoPrisco\NovaMongoDB\Traits;

use FrancescoPrisco\NovaMongoDB\Models\NovaNotification;
use Illuminate\Notifications\Notifiable;

trait MongoNotifiable
{
    use Notifiable;

    /**
     * Get the entity's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(NovaNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's unread notifications.
     */
    public function unreadNotifications()
    {
        return $this->morphMany(NovaNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }
}
