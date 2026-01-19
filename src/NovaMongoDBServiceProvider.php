<?php

namespace FrancescoPrisco\NovaMongoDB;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class NovaMongoDBServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nova-mongodb.php', 'nova-mongodb'
        );

        // Register custom MongoDB connection that handles nested transactions
        $this->app->resolving('db', function ($db) {
            $db->extend('mongodb', function ($config, $name) {
                $config['name'] = $name;
                return new \FrancescoPrisco\NovaMongoDB\MongoDBConnection($config);
            });
        });

        // Register the MongoDB action event model
        $this->app->bind(
            \Laravel\Nova\Models\ActionEvent::class,
            \FrancescoPrisco\NovaMongoDB\Models\ActionEvent::class
        );

        // Override database notification model
        $this->app->bind(
            \Illuminate\Notifications\DatabaseNotification::class,
            \FrancescoPrisco\NovaMongoDB\Models\NovaNotification::class
        );
        
        // Also bind the concrete class
        $this->app->singleton('nova.notification', function ($app) {
            return \FrancescoPrisco\NovaMongoDB\Models\NovaNotification::class;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/nova-mongodb.php' => config_path('nova-mongodb.php'),
            ], 'nova-mongodb-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'nova-mongodb-migrations');
        }

        // Register macros for Nova compatibility
        $this->registerMacros();
        
        // Override Nova notification routes with higher priority
        $this->registerNovaNotificationRoutes();
        
        // Register Model Observer for ActionEvent logging
        $this->registerModelObservers();
    }
    
    /**
     * Register custom notification routes that take precedence over Nova's.
     */
    protected function registerNovaNotificationRoutes(): void
    {
        $this->app->booted(function () {
            $router = $this->app['router'];
            
            // Remove existing Nova notification routes and register ours
            $router->group([
                'domain' => config('nova.domain', null),
                'prefix' => 'nova-api',
                'middleware' => ['nova', 'api'],
            ], function ($router) {
                $router->get('/nova-notifications', function () {
                    $user = auth()->user();
                    
                    if (!$user) {
                        return response()->json([
                            'notifications' => [],
                            'unread' => 0,
                        ]);
                    }
                    
                    $notifications = $user->notifications()
                        ->take(100)
                        ->get()
                        ->map(function ($notification) {
                            $data = $notification->data;
                            return [
                                'id' => (string) $notification->id,
                                'user_id' => (string) $notification->notifiable_id,
                                'component' => $data['component'] ?? null,
                                'message' => $data['message'] ?? $data['title'] ?? 'Notifica',
                                'actionText' => $data['actionText'] ?? $data['action'] ?? null,
                                'actionUrl' => $data['actionUrl'] ?? null,
                                'openInNewTab' => $data['openInNewTab'] ?? false,
                                'icon' => $data['icon'] ?? 'bell',
                                'type' => $data['type'] ?? 'info',
                                'iconClass' => $data['iconClass'] ?? null,
                                'created_at_friendly' => $notification->created_at->diffForHumans(),
                                'created_at' => $notification->created_at->toIso8601String(),
                                'read_at' => $notification->read_at,
                            ];
                        });
                    
                    $unreadCount = $user->unreadNotifications()->count();
                    
                    return response()->json([
                        'notifications' => $notifications,
                        'unread' => $unreadCount,
                    ]);
                });
                
                $router->post('/nova-notifications/{notification}/read', function ($notificationId) {
                    $user = auth()->user();
                    $notification = $user->notifications()->where('_id', $notificationId)->first();
                    
                    if ($notification) {
                        $notification->markAsRead();
                    }
                    
                    return response()->noContent();
                });
                
                $router->post('/nova-notifications/{notification}/unread', function ($notificationId) {
                    $user = auth()->user();
                    $notification = $user->notifications()->where('_id', $notificationId)->first();
                    
                    if ($notification) {
                        $notification->markAsUnread();
                    }
                    
                    return response()->noContent();
                });
                
                $router->post('/nova-notifications/read-all', function () {
                    auth()->user()->unreadNotifications()->update(['read_at' => now()]);
                    return response()->noContent();
                });
                
                $router->delete('/nova-notifications/{notification}', function ($notificationId) {
                    auth()->user()->notifications()->where('_id', $notificationId)->delete();
                    return response()->noContent();
                });
                
                $router->delete('/nova-notifications', function () {
                    auth()->user()->notifications()->delete();
                    return response()->noContent();
                });
            });
        });
    }

    /**
     * Register Model observers for ActionEvent logging.
     */
    protected function registerModelObservers(): void
    {
        // Register observer for specific MongoDB models
        $this->app->booted(function () {
            // Get all models from the app/Models directory
            $modelsPath = app_path('Models');
            
            if (file_exists($modelsPath)) {
                $files = glob($modelsPath . '/*.php');
                
                foreach ($files as $file) {
                    $modelName = 'App\\Models\\' . basename($file, '.php');
                    
                    if (class_exists($modelName)) {
                        // Check if it's a MongoDB model (either Eloquent\Model or Auth\User)
                        $isMongoModel = is_subclass_of($modelName, \MongoDB\Laravel\Eloquent\Model::class) ||
                                       is_subclass_of($modelName, \MongoDB\Laravel\Auth\User::class);
                        
                        if ($isMongoModel) {
                            try {
                                $modelName::observe(\FrancescoPrisco\NovaMongoDB\Observers\ModelObserver::class);
                            } catch (\Exception $e) {
                                // Skip if registration fails
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Register MongoDB-specific macros for Nova.
     */
    protected function registerMacros(): void
    {
        // Override Nova's query methods to work with MongoDB
        \Illuminate\Database\Query\Builder::macro('toBase', function () {
            return $this;
        });
    }
}
