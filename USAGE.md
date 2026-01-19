# Guida all'utilizzo di Laravel Nova MongoDB

## Struttura del Pacchetto

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

## Installazione

### Via Composer

```bash
composer require francescoprisco/nova-mongodb
```

Il service provider viene registrato automaticamente tramite Laravel package discovery.

### Pubblica la configurazione (opzionale)

```bash
php artisan vendor:publish --tag=nova-mongodb-config
```

### Configura MongoDB

Assicurati di avere MongoDB configurato in `config/database.php`:

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

## Utilizzo Base

### Creare una Resource MongoDB

Invece di estendere `Laravel\Nova\Resource`, estendi `FrancescoPrisco\NovaMongoDB\MongoDBResource`:

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

### Configurare il Model MongoDB

Il tuo model deve estendere `MongoDB\Laravel\Eloquent\Model`:

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

## Funzionalità

### 1. Ricerca (Search)

Il pacchetto converte automaticamente le ricerche in regex MongoDB case-insensitive:

```php
public static $search = [
    'name',
    'email',
    'description',
];
```

La ricerca supporta match parziali e è case-insensitive.

### 2. Ordinamento (Sorting)

Funziona normalmente con MongoDB:

```php
Text::make('Name')->sortable()
```

### 3. Filtri

I filtri standard di Nova sono compatibili.

### 4. Relazioni Polimorfiche

Il pacchetto gestisce automaticamente le relazioni polimorfiche MongoDB.

### 5. Action Events

Il sistema registra automaticamente tutti gli eventi tramite un Observer:

- **Create**: Logging automatico alla creazione
- **Update**: Traccia tutte le modifiche (original vs changes)
- **Delete**: Log delle eliminazioni

Gli eventi vengono salvati nella collection `action_events` con:
- `batch_id`: UUID per raggruppare operazioni
- `user_id`: Utente che ha eseguito l'azione
- `name`: Tipo di azione (Create, Update, Delete)
- `model_type` e `model_id`: Riferimenti al model
- `original`: Valori prima della modifica
- `changes`: Valori dopo la modifica
- `status`: Stato dell'operazione
- `created_at`: Timestamp

### 6. Notifiche

Sistema completo di notifiche Nova:

```php
// Nel tuo User model
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable;
}

// Invia notifica
$user->notify(new YourNotification($data));

// Le notifiche appariranno automaticamente in Nova con:
// - Badge con conteggio non lette
// - Mark as read/unread
// - Elimina singola/tutte
```

### 7. Transaction Handling

Il pacchetto gestisce automaticamente le transazioni nested MongoDB prevenendo errori "Transaction already in progress".

## Esempio Completo: Bookings Resource

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

## Limitazioni Attuali

1. **Lenses**: Le Lenses che usano query SQL complesse potrebbero richiedere adattamento per MongoDB.

2. **Metrics Complesse**: Metrics con aggregazioni SQL avanzate vanno riscritte usando MongoDB aggregation pipeline.

3. **Scout Search**: Laravel Scout richiede un driver MongoDB custom per ricerca full-text avanzata.

## Testing

Per testare le risorse:

```bash
php artisan tinker
```

```php
// Crea un booking di test
$booking = new \App\Models\Bookings();
$booking->customer_name = 'Test Customer';
$booking->booking_date = now();
$booking->status = 'pending';
$booking->notes = 'Test booking';
$booking->save();

// Verifica che sia visibile in Nova
\App\Models\Bookings::count(); // Dovrebbe essere > 0

// Verifica che l'ActionEvent sia stato creato
FrancescoPrisco\NovaMongoDB\Models\ActionEvent::where('model_type', 'App\\Models\\Bookings')
    ->where('name', 'Create')
    ->count(); // Dovrebbe essere > 0
```

## Troubleshooting

### Collection non trovata

Verifica che il nome della collection nel model corrisponda a quella nel database:

```php
protected $collection = 'bookings'; // Nome esatto della collection
```

### Notifiche non funzionano

Verifica che il model User usi il trait `MongoNotifiable`:

```php
use FrancescoPrisco\NovaMongoDB\Traits\MongoNotifiable;

class User extends Authenticatable
{
    use MongoNotifiable;
}
```

## Prossimi Sviluppi

- [ ] Resource viewer per ActionEvents nella UI Nova
- [ ] Metrics e dashboard widgets ottimizzati per MongoDB aggregation
- [ ] Scout driver per ricerca full-text MongoDB
- [ ] Support per Lenses personalizzate
- [ ] Cache layer per query complesse
- [ ] Testing suite completo con PHPUnit

## Contribuire

Contributions are welcome! Per contribuire:

1. Fork del repository su GitHub
2. Crea un branch per la tua feature (`git checkout -b feature/amazing-feature`)
3. Commit delle modifiche (`git commit -m 'Add amazing feature'`)
4. Push al branch (`git push origin feature/amazing-feature`)
5. Apri una Pull Request

## Supporto

Per issue, domande o richieste di feature:
- GitHub Issues: https://github.com/francescoprisco/nova-mongodb/issues
- Email: francesco.prisco@generazioneai.it

## License

MIT License - Libero per uso commerciale e personale.

Copyright (c) 2026 Francesco Prisco
