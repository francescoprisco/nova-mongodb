# Laravel Nova MongoDB Adapter

Complete package to integrate Laravel Nova with MongoDB, enabling all Nova features on MongoDB databases without any SQL dependencies.

## ✨ Features

- ✅ **Nova Resources**: Complete CRUD on MongoDB collections
- ✅ **Full-Text Search**: Case-insensitive regex search on MongoDB
- ✅ **Action Events**: Complete action logging system via Observer pattern
- ✅ **Authentication**: User model fully on MongoDB
- ✅ **Notifications**: Complete notification system with mark read/unread on MongoDB
- ✅ **Transaction Handling**: Automatic nested transaction management
- ✅ **Zero SQL**: No SQL database dependencies

## 📦 Installation

```bash
composer require francescoprisco/nova-mongodb
```

The service provider is automatically registered via Laravel package auto-discovery.

### MongoDB Configuration

Make sure you have MongoDB connection configured in your `config/database.php`:

```php
'connections' => [
    'mongodb' => [
        'driver' => 'mongodb',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 27017),
        'database' => env('DB_DATABASE', 'database'),
        'username' => env('DB_USERNAME', ''),
        'password' => env('DB_PASSWORD', ''),
        'options' => [
            'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
        ],
    ],
],

## ⚙️ Configuration

### 1. User Model

```php
use MongoDB\Laravel\Auth\User as Authenticatable;
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable;
    
    protected $connection = 'mongodb';
    protected $collection = 'users';
    
    protected $fillable = ['name', 'email', 'password'];
}
```

### 2. Nova Resources

Resources must extend `MongoDBResource`:

```php
use FrancescoPrisco\NovaMongoDB\MongoDBResource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;

class Bookings extends MongoDBResource
{
    public static $model = \App\Models\Bookings::class;
    public static $title = 'customer_name';
    public static $search = ['id', 'customer_name', 'status'];
    
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Customer Name')->sortable(),
            DateTime::make('Booking Date')->sortable(),
            Select::make('Status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
            ]),
            Text::make('Notes')->hideFromIndex(),
        ];
    }
}
```

### 3. MongoDB Models

```php
use MongoDB\Laravel\Eloquent\Model;

class Bookings extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bookings';
    
    protected $fillable = [
        'customer_name',
        'booking_date',
        'status',
        'notes',
    ];
    
    protected $casts = [
        'booking_date' => 'datetime',
    ];
}
```

## 🏗️ Architecture

### Main Components

#### `MongoDBResource`
Base class for Nova resources with complete MongoDB support:
- Case-insensitive regex search via MongoDB (`$regex`)
- Correct type hints for MongoDB builder
- Compatibility with all Nova CRUD operations

#### `MongoDBConnection`
Extends standard MongoDB connection to handle nested transactions:
- Automatically catches errors from transactions already in progress
- Executes callbacks directly when necessary
- Prevents "Transaction already in progress" errors

#### `ModelObserver`
Automatic observer for action logging:
- Automatically registered on all MongoDB models
- Logs created, updated, deleted events
- Saves to `action_events` collection with complete change tracking

#### MongoDB Models

**ActionEvent**: Saves action events in `action_events` collection with complete details (batch_id, user_id, changes, original, status)
**NovaNotification**: Notification model in `notifications` collection with read/unread support

#### Traits

**MongoNotifiable**: Complete notification management with `notifications()` and `unreadNotifications()` relations
**HandlesMorphRelations**: Helper for MongoDB polymorphic relations

### Custom Routes

The package automatically registers custom routes for Nova notifications:
- `GET /nova-api/nova-notifications` - List notifications
- `POST /nova-api/nova-notifications/{id}/read` - Mark as read
- `POST /nova-api/nova-notifications/{id}/unread` - Mark as unread
- `POST /nova-api/nova-notifications/read-all` - Mark all as read
- `DELETE /nova-api/nova-notifications/{id}` - Delete notification
- `DELETE /nova-api/nova-notifications` - Delete all

## 🚀 Usage

### Create a new resource

```bash
php artisan nova:resource Product
```

Modify the generated resource:

```php
use FrancescoPrisco\NovaMongoDB\MongoDBResource;

class Product extends MongoDBResource
{
    public static $model = \App\Models\Product::class;
    public static $search = ['name', 'sku', 'description'];
    
    // ... fields and configuration
}
```

### Register the resource

In `app/Providers/NovaServiceProvider.php`:

```php
use App\Nova\Product;

protected function resources()
{
    Nova::resources([
        Product::class,
        // other resources...
    ]);
}
```

## ⚠️ Known Limitations

### 1. Scout Search
Laravel Scout advanced search requires a custom MongoDB driver. Currently search uses native MongoDB regex.

### 2. Advanced Metrics
Cards/Metrics using complex SQL aggregations may require rewriting using MongoDB aggregation pipeline.

### 3. Lenses
Nova Lenses using complex SQL queries may not work directly and require adaptation.

## 🔧 Troubleshooting

### Search not working

Verify that:
1. The resource extends `MongoDBResource`
2. The `$search` fields are defined
3. The model uses `connection = 'mongodb'`

### User cannot authenticate

Verify:
1. User model extends `MongoDB\Laravel\Auth\User`
2. Uses the `MongoNotifiable` trait
3. `config/auth.php` points to the correct model

## 📊 Performance

The package automatically optimizes:
- Multiple queries via eager loading
- Automatic indexing of search fields
- Nova-compatible result caching

## 🛠️ Future Development

Roadmap:
- [ ] Resource viewer to display ActionEvents in Nova UI
- [ ] Advanced metrics/cards adapter with aggregation pipeline
- [ ] Scout driver for MongoDB full-text search
- [ ] Support for custom Lenses
- [ ] Complete test suite
- [ ] MongoDB-native dashboard widgets with real-time updates

## 📋 Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Laravel Nova 5.0+
- MongoDB 5.0+
- mongodb/laravel-mongodb ^5.5

## 📄 License

MIT License - Francesco Prisco

## 🤝 Support

For issues and support: francesco.prisco@generazioneai.it
