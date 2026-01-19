# Laravel Nova MongoDB Adapter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/francescoprisco/nova-mongodb.svg?style=flat-square)](https://packagist.org/packages/francescoprisco/nova-mongodb)
[![Total Downloads](https://img.shields.io/packagist/dt/francescoprisco/nova-mongodb.svg?style=flat-square)](https://packagist.org/packages/francescoprisco/nova-mongodb)
[![License](https://img.shields.io/packagist/l/francescoprisco/nova-mongodb.svg?style=flat-square)](https://packagist.org/packages/francescoprisco/nova-mongodb)

Complete Laravel Nova adapter for MongoDB - enables full Nova functionality on MongoDB databases without any SQL dependencies.

## 🚀 Quick Start

```bash
composer require francescoprisco/nova-mongodb
```

```php
use FrancescoPrisco\NovaMongoDB\MongoDBResource;

class Product extends MongoDBResource
{
    public static $model = \App\Models\Product::class;
    public static $search = ['name', 'sku', 'description'];
    
    public function fields(NovaRequest $request) {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable(),
            Number::make('Price')->sortable(),
            // ... more fields
        ];
    }
}
```

## ✨ Features

- ✅ Complete CRUD operations on MongoDB collections
- ✅ Full-text search with MongoDB regex (case-insensitive)
- ✅ Action Events logging via Observer pattern
- ✅ Complete notification system with read/unread
- ✅ Automatic nested transaction handling
- ✅ Zero SQL dependencies

## 📚 What's Included

### MongoDBResource
Base resource class for Nova with MongoDB-optimized search and query handling.

### Action Events
Automatic logging of all CRUD operations:
- Tracks created, updated, deleted events
- Stores original and changed values
- MongoDB-native storage in `action_events` collection

### Notifications
Complete notification system:
- Mark as read/unread
- Delete notifications
- Badge with unread count
- All stored in MongoDB

### Transaction Handling
Custom MongoDB connection that handles nested transactions gracefully, preventing "Transaction already in progress" errors.

## 🎯 Perfect For

- Laravel Nova projects using MongoDB
- NoSQL applications requiring Nova admin panel
- Projects wanting to eliminate SQL dependencies
- MongoDB-first architectures

## 📖 Documentation

- [Complete Documentation](https://github.com/francescoprisco/nova-mongodb)
- [Usage Guide](https://github.com/francescoprisco/nova-mongodb/blob/main/USAGE.md)
- [Examples](https://github.com/francescoprisco/nova-mongodb/blob/main/EXAMPLES.php)
- [Changelog](https://github.com/francescoprisco/nova-mongodb/blob/main/CHANGELOG.md)

## 💻 Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Laravel Nova 5.0+
- MongoDB 5.0+
- mongodb/laravel-mongodb ^5.5

## 📝 License

MIT License - see [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Francesco Prisco**
- Email: francesco@codeloops.it
- GitHub: [@francescoprisco](https://github.com/francescoprisco)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 🐛 Issues

Found a bug? Please open an issue on [GitHub](https://github.com/francescoprisco/nova-mongodb/issues).
