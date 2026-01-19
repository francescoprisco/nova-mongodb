<?php

/**
 * Esempio di utilizzo completo di francescoprisco/nova-mongodb
 * 
 * Questo file mostra come configurare e usare tutte le funzionalità del pacchetto
 */

// ============================================
// 1. CONFIGURAZIONE MONGODB
// ============================================

// In config/database.php
'connections' => [
    'mongodb' => [
        'driver' => 'mongodb',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 27017),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        'options' => [
            'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
        ],
    ],
];

// ============================================
// 2. MODEL MONGODB
// ============================================

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';
    
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock',
        'category',
        'status',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

// ============================================
// 3. NOVA RESOURCE
// ============================================

namespace App\Nova;

use FrancescoPrisco\NovaMongoDB\MongoDBResource;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends MongoDBResource
{
    public static $model = \App\Models\Product::class;
    
    public static $title = 'name';
    
    // Campi ricercabili (usa regex MongoDB)
    public static $search = [
        'id',
        'name',
        'sku',
        'description',
    ];
    
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),
            
            Text::make('SKU')
                ->sortable()
                ->rules('required', 'unique:products,sku')
                ->creationRules('unique:products,sku')
                ->updateRules('unique:products,sku,{{resourceId}}'),
            
            Textarea::make('Description')
                ->hideFromIndex()
                ->rules('nullable', 'max:1000'),
            
            Number::make('Price')
                ->sortable()
                ->rules('required', 'numeric', 'min:0')
                ->step(0.01),
            
            Number::make('Stock')
                ->sortable()
                ->rules('required', 'integer', 'min:0'),
            
            Select::make('Category')->options([
                'electronics' => 'Electronics',
                'clothing' => 'Clothing',
                'food' => 'Food',
                'books' => 'Books',
                'other' => 'Other',
            ])->sortable(),
            
            Select::make('Status')->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
                'discontinued' => 'Discontinued',
            ])->sortable()
              ->default('active'),
            
            DateTime::make('Created At')
                ->sortable()
                ->onlyOnDetail(),
            
            DateTime::make('Updated At')
                ->sortable()
                ->onlyOnDetail(),
        ];
    }
    
    public function filters(NovaRequest $request): array
    {
        return [
            // Puoi aggiungere filtri custom qui
        ];
    }
    
    public function actions(NovaRequest $request): array
    {
        return [
            // Puoi aggiungere azioni custom qui
        ];
    }
}

// ============================================
// 4. USER MODEL CON NOTIFICHE
// ============================================

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable; // Trait per notifiche MongoDB
    
    protected $connection = 'mongodb';
    protected $collection = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}

// ============================================
// 5. INVIARE NOTIFICHE
// ============================================

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class OrderShipped extends Notification
{
    public function via($notifiable): array
    {
        return ['database'];
    }
    
    public function toDatabase($notifiable): array
    {
        return [
            'component' => 'notification',
            'message' => 'Your order has been shipped!',
            'actionText' => 'View Order',
            'actionUrl' => '/orders/123',
            'type' => 'success',
            'icon' => 'check-circle',
        ];
    }
}

// Invia notifica
$user = User::find($userId);
$user->notify(new OrderShipped());

// Le notifiche appariranno automaticamente in Nova!

// ============================================
// 6. VERIFICARE ACTION EVENTS
// ============================================

use FrancescoPrisco\NovaMongoDB\Models\ActionEvent;

// Ottieni tutti gli action events
$events = ActionEvent::all();

// Filtra per model
$productEvents = ActionEvent::where('model_type', 'App\Models\Product')->get();

// Eventi per un record specifico
$productId = '507f1f77bcf86cd799439011';
$events = ActionEvent::where('model_type', 'App\Models\Product')
    ->where('model_id', $productId)
    ->orderBy('created_at', 'desc')
    ->get();

// Visualizza le modifiche
foreach ($events as $event) {
    echo "Action: {$event->name}\n";
    echo "User: {$event->user_id}\n";
    echo "Changes:\n";
    print_r($event->changes);
    echo "Original:\n";
    print_r($event->original);
}

// ============================================
// 7. REGISTRARE LA RESOURCE IN NOVA
// ============================================

// In app/Providers/NovaServiceProvider.php

use App\Nova\Product;

protected function resources()
{
    Nova::resources([
        Product::class,
        // altre risorse...
    ]);
}

// ============================================
// 8. TESTING
// ============================================

use App\Models\Product;
use FrancescoPrisco\NovaMongoDB\Models\ActionEvent;

// Crea un prodotto
$product = Product::create([
    'name' => 'Test Product',
    'sku' => 'TEST-001',
    'description' => 'A test product',
    'price' => 99.99,
    'stock' => 10,
    'category' => 'electronics',
    'status' => 'active',
]);

// Verifica che l'ActionEvent sia stato creato
$event = ActionEvent::where('model_type', 'App\Models\Product')
    ->where('model_id', (string) $product->_id)
    ->where('name', 'Create')
    ->first();

if ($event) {
    echo "✅ Action Event creato correttamente!\n";
    echo "Batch ID: {$event->batch_id}\n";
    echo "User ID: {$event->user_id}\n";
}

// Aggiorna il prodotto
$product->price = 89.99;
$product->stock = 5;
$product->save();

// Verifica l'evento di update
$updateEvent = ActionEvent::where('model_type', 'App\Models\Product')
    ->where('model_id', (string) $product->_id)
    ->where('name', 'Update')
    ->first();

if ($updateEvent) {
    echo "✅ Update Event creato!\n";
    echo "Changes: " . json_encode($updateEvent->changes) . "\n";
    echo "Original: " . json_encode($updateEvent->original) . "\n";
}

// ============================================
// 9. QUERY MONGODB AVANZATE
// ============================================

// Le query MongoDB funzionano normalmente
$products = Product::where('price', '>', 50)
    ->where('stock', '>', 0)
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->get();

// Aggregazioni
$totalValue = Product::where('status', 'active')
    ->get()
    ->sum(function ($product) {
        return $product->price * $product->stock;
    });

// Ricerca regex (usata automaticamente da Nova)
$products = Product::where('name', 'regex', '/laptop/i')->get();

// ============================================
// 10. RELAZIONI MONGODB
// ============================================

// In un model Order
public function products()
{
    return $this->embedsMany(Product::class);
}

// In un model Product
public function category()
{
    return $this->belongsTo(Category::class);
}

// Relazioni Many-to-Many con array di IDs
protected $casts = [
    'tag_ids' => 'array',
];

public function tags()
{
    return Tag::whereIn('_id', $this->tag_ids ?? [])->get();
}
