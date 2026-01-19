# Guida Pubblicazione Package

## 📦 Pacchetto Pronto

Il pacchetto `francescoprisco/nova-mongodb` v1.0.0 è pronto per la pubblicazione!

### ✅ Checklist Completata

- [x] Codice sorgente completo e funzionante
- [x] README.md aggiornato con documentazione completa
- [x] USAGE.md con guida dettagliata all'uso
- [x] CHANGELOG.md con history versioni
- [x] LICENSE MIT
- [x] composer.json configurato correttamente
- [x] .gitignore per escludere vendor/
- [x] Repository Git inizializzato
- [x] Commit iniziale creato
- [x] Tag v1.0.0 creato

## 🚀 Pubblicazione su GitHub

### 1. Crea Repository su GitHub

Vai su https://github.com/new e crea un nuovo repository:

- **Repository name**: `nova-mongodb`
- **Description**: Complete Laravel Nova adapter for MongoDB - enables full Nova functionality on MongoDB databases without SQL dependencies
- **Visibility**: Public
- **NON inizializzare** con README, .gitignore o LICENSE (li abbiamo già)

### 2. Push del Codice

Dalla directory del package esegui:

```bash
cd /var/www/vhosts/codeloops.it/test.codeloops.it/packages/nova-mongodb

# Aggiungi remote GitHub (sostituisci 'francescoprisco' con il tuo username se diverso)
git remote add origin https://github.com/francescoprisco/nova-mongodb.git

# Push del codice e dei tag
git branch -M main
git push -u origin main
git push origin v1.0.0
```

### 3. Crea Release su GitHub

1. Vai su: https://github.com/francescoprisco/nova-mongodb/releases/new
2. Compila:
   - **Tag**: v1.0.0 (seleziona il tag esistente)
   - **Release title**: v1.0.0 - Initial Release
   - **Description**: Copia il contenuto da CHANGELOG.md
3. Clicca "Publish release"

## 📮 Pubblicazione su Packagist

### 1. Registrati su Packagist

Se non hai già un account:
- Vai su https://packagist.org/
- "Sign Up" in alto a destra
- Puoi usare GitHub OAuth per velocizzare

### 2. Submit Package

1. Vai su https://packagist.org/packages/submit
2. Incolla l'URL del repository: `https://github.com/francescoprisco/nova-mongodb`
3. Clicca "Check"
4. Packagist verificherà automaticamente il composer.json
5. Clicca "Submit"

### 3. Configura Auto-Update (Consigliato)

Per aggiornare automaticamente Packagist a ogni push:

1. Vai su https://packagist.org/packages/francescoprisco/nova-mongodb
2. Clicca su "Settings" in alto
3. Copia il webhook URL e il token
4. Su GitHub vai in: Repository → Settings → Webhooks → Add webhook
5. Incolla:
   - **Payload URL**: l'URL di Packagist
   - **Content type**: application/json
   - **Secret**: il token di Packagist
6. Seleziona "Just the push event"
7. Clicca "Add webhook"

## 📢 Annuncio

Dopo la pubblicazione, puoi annunciare il pacchetto su:

- [Laravel News](https://laravel-news.com/submit-a-package)
- [Laravel.io Forum](https://laravel.io/forum)
- [Reddit r/laravel](https://reddit.com/r/laravel)
- Twitter/X con hashtags #Laravel #Nova #MongoDB
- LinkedIn

### Template Post

```
🚀 Nuovo pacchetto Laravel Nova + MongoDB!

Ho appena rilasciato nova-mongodb v1.0.0 - un adapter completo per usare Laravel Nova con MongoDB senza SQL!

✨ Features:
✅ CRUD completo su MongoDB
✅ Action Events con Observer pattern
✅ Sistema notifiche completo
✅ Transaction handling automatico
✅ Zero dipendenze SQL

📦 composer require francescoprisco/nova-mongodb

🔗 https://github.com/francescoprisco/nova-mongodb
🔗 https://packagist.org/packages/francescoprisco/nova-mongodb

#Laravel #Nova #MongoDB #PHP
```

## 🔄 Aggiornamenti Futuri

Per rilasciare una nuova versione:

```bash
# Fai le modifiche
git add -A
git commit -m "Description of changes"

# Aggiorna versione in composer.json
# Aggiorna CHANGELOG.md

# Crea nuovo tag
git tag -a v1.1.0 -m "Release v1.1.0 - New features"

# Push
git push origin main
git push origin v1.1.0

# Crea release su GitHub
```

Packagist si aggiornerà automaticamente se hai configurato il webhook!

## 📊 Monitoraggio

Dopo la pubblicazione, monitora:

- **GitHub**: Stars, Issues, Pull Requests
- **Packagist**: Downloads giornalieri/mensili
- **Packagist Installs**: https://packagist.org/packages/francescoprisco/nova-mongodb/stats

## ❓ Support

Per questioni tecniche:
- GitHub Issues: https://github.com/francescoprisco/nova-mongodb/issues
- Email: francesco@codeloops.it

Buona pubblicazione! 🎉
