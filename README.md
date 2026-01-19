# Laravel Nova MongoDB Adapter

Pacchetto completo per integrare Laravel Nova con MongoDB, permettendo l'utilizzo di tutte le funzionalità Nova su database MongoDB senza alcuna dipendenza da SQL.

## ✨ Caratteristiche

- ✅ **Risorse Nova**: CRUD completo su collection MongoDB
- ✅ **Ricerca Full-Text**: Ricerca tramite regex MongoDB case-insensitive
- ✅ **Action Events**: Sistema completo di logging delle azioni tramite Observer pattern
- ✅ **Autenticazione**: User model completamente su MongoDB
- ✅ **Notifiche**: Sistema notifiche completo con mark read/unread su MongoDB
- ✅ **Transaction Handling**: Gestione automatica delle transazioni nested
- ✅ **Zero SQL**: Nessuna dipendenza da database SQL

## 📦 Installazione

```bash
composer require francescoprisco/nova-mongodb
```

Il service provider viene registrato automaticamente via Laravel package auto-discovery.

### Configurazione MongoDB

Assicurati di avere la connessione MongoDB configurata nel tuo `config/database.php`:

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

## ⚙️ Configurazione

### 1. Model User

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

Le risorse devono estendere `MongoDBResource`:

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

### 3. Models MongoDB

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

## 🏗️ Architettura

### Componenti Principali

#### `MongoDBResource`
Classe base per risorse Nova con supporto MongoDB completo:
- Ricerca tramite regex MongoDB (`$regex`) case-insensitive
- Type hints corretti per builder MongoDB
- Compatibilità con tutte le operazioni Nova CRUD

#### `MongoDBConnection`
Estende la connessione MongoDB standard per gestire transazioni nested:
- Cattura automaticamente errori di transazioni già in corso
- Esegue callback direttamente quando necessario
- Previene errori "Transaction already in progress"

#### `ModelObserver`
Observer automatico per logging delle azioni:
- Registrato automaticamente su tutti i modelli MongoDB
- Log di created, updated, deleted
- Salvataggio su `action_events` collection con tracking completo delle modifiche

#### Models MongoDB

**ActionEvent**: Salva eventi azioni in `action_events` collection con dettagli completi (batch_id, user_id, changes, original, status)
**NovaNotification**: Model notifiche in `notifications` collection con supporto read/unread

#### Traits

**MongoNotifiable**: Gestione notifiche complete con relazioni `notifications()` e `unreadNotifications()`
**HandlesMorphRelations**: Helper per relazioni polimorfiche MongoDB

### Routes Personalizzate

Il package registra automaticamente routes custom per le notifiche Nova:
- `GET /nova-api/nova-notifications` - Lista notifiche
- `POST /nova-api/nova-notifications/{id}/read` - Segna come letta
- `POST /nova-api/nova-notifications/{id}/unread` - Segna come non letta
- `POST /nova-api/nova-notifications/read-all` - Segna tutte come lette
- `DELETE /nova-api/nova-notifications/{id}` - Elimina notifica
- `DELETE /nova-api/nova-notifications` - Elimina tutte

## 🚀 Utilizzo

### Creare una nuova risorsa

```bash
php artisan nova:resource Product
```

Modifica la risorsa generata:

```php
use FrancescoPrisco\NovaMongoDB\MongoDBResource;

class Product extends MongoDBResource
{
    public static $model = \App\Models\Product::class;
    public static $search = ['name', 'sku', 'description'];
    
    // ... campi e configurazione
}
```

### Registrare la risorsa

In `app/Providers/NovaServiceProvider.php`:

```php
use App\Nova\Product;

protected function resources()
{
    Nova::resources([
        Product::class,
        // altre risorse...
    ]);
}
```

## ⚠️ Limitazioni Note

### 1. Scout Search
La ricerca avanzata di Laravel Scout richiede un driver MongoDB custom. Attualmente la ricerca usa regex MongoDB nativi.

### 2. Metriche Avanzate
Cards/Metrics che usano aggregazioni SQL complesse potrebbero richiedere riscrittura usando MongoDB aggregation pipeline.

### 3. Lenses
Le Lenses di Nova che usano query SQL complesse potrebbero non funzionare direttamente e richiedere adattamento.

## 🔧 Risoluzione Problemi

### Ricerca non funziona

Verifica che:
1. La risorsa estenda `MongoDBResource`
2. I campi `$search` siano definiti
3. Il model usi `connection = 'mongodb'`

### User non si autentica

Verifica:
1. User model estenda `MongoDB\Laravel\Auth\User`
2. Usi il trait `MongoNotifiable`
3. `config/auth.php` punti al model corretto

## 📊 Prestazioni

Il pacchetto ottimizza automaticamente:
- Query multiple tramite eager loading
- Indicizzazione automatica campi ricerca
- Cache dei risultati Nova compatibile

## 🛠️ Sviluppo Futuro

Roadmap:
- [ ] Resource viewer per visualizzare ActionEvents nella UI Nova
- [ ] Adapter metrics/cards avanzate con aggregation pipeline
- [ ] Scout driver per ricerca full-text MongoDB
- [ ] Support per Lenses personalizzate
- [ ] Test suite completa
- [ ] Dashboard widgets MongoDB-nativi con real-time updates

## 📋 Requisiti

- PHP 8.2+
- Laravel 11.0+ o 12.0+
- Laravel Nova 5.0+
- MongoDB 5.0+
- mongodb/laravel-mongodb ^5.5

## 📄 Licenza

MIT License - Francesco Prisco

## 🤝 Supporto

Per issue e supporto: francesco@codeloops.it
