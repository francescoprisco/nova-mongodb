<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MongoDB Connection
    |--------------------------------------------------------------------------
    |
    | The MongoDB connection to use for Nova data.
    | If null, it will use the default connection.
    |
    */
    'connection' => env('NOVA_MONGODB_CONNECTION', 'mongodb'),

    /*
    |--------------------------------------------------------------------------
    | Action Events Collection
    |--------------------------------------------------------------------------
    |
    | The collection name for storing Nova action events.
    |
    */
    'action_events_collection' => env('NOVA_ACTION_EVENTS_COLLECTION', 'action_events'),

    /*
    |--------------------------------------------------------------------------
    | Notifications Collection
    |--------------------------------------------------------------------------
    |
    | The collection name for storing Nova notifications.
    |
    */
    'notifications_collection' => env('NOVA_NOTIFICATIONS_COLLECTION', 'nova_notifications'),

    /*
    |--------------------------------------------------------------------------
    | Field Attachments Collection
    |--------------------------------------------------------------------------
    |
    | The collection name for storing Nova field attachments.
    |
    */
    'field_attachments_collection' => env('NOVA_FIELD_ATTACHMENTS_COLLECTION', 'nova_field_attachments'),

    /*
    |--------------------------------------------------------------------------
    | Enable Polymorphic Support
    |--------------------------------------------------------------------------
    |
    | Enable special handling for polymorphic relationships in MongoDB.
    |
    */
    'polymorphic_support' => true,

    /*
    |--------------------------------------------------------------------------
    | Index Configuration
    |--------------------------------------------------------------------------
    |
    | Configure indexes for better MongoDB performance.
    |
    */
    'indexes' => [
        'action_events' => [
            ['batch_id' => 1, 'model_type' => 1, 'model_id' => 1],
            ['user_id' => 1],
        ],
        'nova_notifications' => [
            ['notifiable_type' => 1, 'notifiable_id' => 1],
            ['read_at' => 1],
        ],
    ],
];
