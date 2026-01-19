# Laravel Nova MongoDB Usage Guide

## Package Structure

```
packages/nova-mongodb/
├── composer.json
├── README.md
├── config/
│   └── nova-mongodb.php
└── src/
    ├── NovaMongoDBServiceProvider.php
    ├── MongoDBResource.php
    ├── MongoDBConnection.php
    ├── Models/
    │   ├── ActionEvent.php
    │   └── NovaNotification.php
    ├── Observers/
    │   └── ModelObserver.php
    └── Traits/
        ├── HandlesMorphRelations.php
        └── MongoNotifiable.php
```

## Installation

### Via Composer

```bash
composer require francescoprisco/nova-mongodb
```

The service provider is automatically registered via Laravel package discovery.

### Publish Configuration (optional)

```bash
php artisan vendor:publish --tag=nova-mongodb-config
```

### Configure MongoDB

Make sure MongoDB is configured in `config/database.php`:

```php
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 27017),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],
```

E nel tuo `.env`:

```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Basic Usage

### Create a MongoDB Resource

Instead of extending `Laravel\Nova\Resource`, extend `FrancescoPrisco\NovaMongoDB\MongoDBResource`:

```php
<?php

namespace App\Nova;

use FrancescoPrisco\NovaMongoDB\MongoDBResource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class YourResource extends MongoDBResource
{
    public static $model = \App\Models\YourModel::class;

    public static $search = [
        'id',
        'name',
        'email',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable(),
            Text::make('Email')->sortable(),
        ];
    }
}
```

### Configure MongoDB Model

Your model must extend `MongoDB\Laravel\Eloquent\Model`:

```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class YourModel extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'your_collection';

    protected $fillable = [
        'name',
        'email',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

## Features

### 1. Search

The package automatically converts searches to case-insensitive MongoDB regex:

```php
public static $search = [
    'name',
    'email',
    'description',
];
```

Search supports partial matching and is case-insensitive.

### 2. Sorting

Works normally with MongoDB:

```php
Text::make('Name')->sortable()
```

### 3. Filters

Standard Nova filters are compatible.

### 4. Polymorphic Relations

The package automatically handles MongoDB polymorphic relations.

### 5. Action Events

The system automatically registers all events via an Observer:

- **Create**: Automatic logging on creation
- **Update**: Tracks all changes (original vs changes)
- **Delete**: Deletion logs

Events are saved in the `action_events` collection with:
- `batch_id`: UUID to group operations
- `user_id`: User who performed the action
- `name`: Action type (Create, Update, Delete)
- `model_type` and `model_id`: Model references
- `original`: Values before modification
- `changes`: Values after modification
- `status`: Operation status
- `created_at`: Timestamp

### 6. Notifications

Complete Nova notification system:

```php
// In your User model
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable;
}

// Send notification
$user->notify(new YourNotification($data));

// Notifications will automatically appear in Nova with:
// - Badge with unread count
// - Mark as read/unread
// - Delete single/all
```

### 7. Transaction Handling

The package automatically handles nested MongoDB transactions preventing "Transaction already in progress" errors.

## Complete Example: Bookings Resource

```php
<?php

namespace App\Nova;

use FrancescoPrisco\NovaMongoDB\MongoDBResource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class Bookings extends MongoDBResource
{
    public static $model = \App\Models\Bookings::class;
    
    public static $title = 'customer_name';
    
    public static $search = [
        'id',
        'customer_name',
        'status',
    ];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            
            Text::make('Customer Name')
                ->sortable()
                ->rules('required', 'max:255'),
            
            DateTime::make('Booking Date')
                ->sortable()
                ->rules('required'),
            
            Select::make('Status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
            ])->sortable(),
            
            Text::make('Notes')
                ->hideFromIndex(),
        ];
    }
}
```

## Current Limitations

1. **Lenses**: Lenses using complex SQL queries may require adaptation for MongoDB.

2. **Complex Metrics**: Metrics with advanced SQL aggregations need to be rewritten using MongoDB aggregation pipeline.

3. **Scout Search**: Laravel Scout requires a custom MongoDB driver for advanced full-text search.

## Testing

To test resources:

```bash
php artisan tinker
```

```php
// Create a test booking
$booking = new \App\Models\Bookings();
$booking->customer_name = 'Test Customer';
$booking->booking_date = now();
$booking->status = 'pending';
$booking->notes = 'Test booking';
$booking->save();

// Verify it's visible in Nova
\App\Models\Bookings::count(); // Should be > 0

// Verify ActionEvent was created
FrancescoPrisco\NovaMongoDB\Models\ActionEvent::where('model_type', 'App\\Models\\Bookings')
    ->where('name', 'Create')
    ->count(); // Should be > 0
```

## Troubleshooting

### Collection not found

Verify that the collection name in the model matches the one in the database:

```php
protected $collection = 'bookings'; // Exact collection name
```

### Notifications not working

Verify that the User model uses the `MongoNotifiable` trait:

```php
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable;
}
```

## Future Developments

- [ ] Resource viewer for ActionEvents in Nova UI
- [ ] Metrics and dashboard widgets optimized for MongoDB aggregation
- [ ] Scout driver for MongoDB full-text search
- [ ] Support for custom Lenses
- [ ] Cache layer for complex queries
- [ ] Complete test suite with PHPUnit

## Contributing

Contributions are welcome! To contribute:

1. Fork the repository on GitHub
2. Create a branch for your feature (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

For issues, questions or feature requests:
- GitHub Issues: https://github.com/francescoprisco/nova-mongodb/issues
- Email: francesco.prisco@generazioneai.it

## License

MIT License - Free for commercial and personal use.

Copyright (c) 2026 Francesco Prisco
