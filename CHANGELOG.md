# Changelog

All notable changes to `francescoprisco/nova-mongodb` will be documented in this file.

## v1.0.0 - 2026-01-19

### Initial Release

#### Features
- ✅ Complete MongoDB support for Laravel Nova resources
- ✅ MongoDBResource base class with regex search
- ✅ Action Events logging via Observer pattern
- ✅ Complete notification system with read/unread support
- ✅ MongoNotifiable trait for User notifications
- ✅ Custom MongoDB connection with nested transaction handling
- ✅ Automatic observer registration for all MongoDB models
- ✅ Custom Nova API routes for notifications
- ✅ Zero SQL dependencies

#### Components
- `MongoDBResource` - Base resource class for Nova
- `MongoDBConnection` - Custom connection handling nested transactions
- `ModelObserver` - Automatic action logging for all CRUD operations
- `ActionEvent` Model - MongoDB-based action event storage
- `NovaNotification` Model - MongoDB-based notification storage
- `MongoNotifiable` Trait - Complete notification support for User models
- `HandlesMorphRelations` Trait - Helper for polymorphic relationships

#### API Routes
- `GET /nova-api/nova-notifications` - List notifications
- `POST /nova-api/nova-notifications/{id}/read` - Mark as read
- `POST /nova-api/nova-notifications/{id}/unread` - Mark as unread
- `POST /nova-api/nova-notifications/read-all` - Mark all as read
- `DELETE /nova-api/nova-notifications/{id}` - Delete notification
- `DELETE /nova-api/nova-notifications` - Delete all

#### Requirements
- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Laravel Nova 5.0+
- MongoDB 5.0+
- mongodb/laravel-mongodb ^5.5
