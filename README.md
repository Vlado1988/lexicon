# Lexicon
**Lexicon** is Laravel-based lightweight dictionary platform. 
It provides basic dictionary functionality allowing users to manage words 
and their definitions. The system is minimal by design — 
it does not include advanced linguistic attributes such 
as articles, parts of speech, or grammatical forms.  
**Lexicon** is ideal as a starting point for small dictionary projects, glossaries, or terminology databases, and its clean Laravel-based architecture allows developers to extend it with additional features if needed.


### 🗂 Admin Panel (Authenticated)
All dictionary management features are available only to authenticated users.


### 🔐 Registration & Authentication
To manage the dictionary content, you must be logged in.

- **Register:** `/register`  
  Create a new user account.

- **Login:** `/login`  
  Access the admin interface.

- **Logout:** from admin panel.


## Prerequisites

Before installing Lexicon, make sure you have the following installed:

* PHP 8.3+
* Composer
* Node.js + npm
* MySQL/MariaDB
* Redis (Optional)
* Git (if cloning from GitHub)

## Features

* Add words and definitions manually
* Import words and definitions from CSV
* Export dictionary data to CSV
* Organize words into categories
* Basic admin panel for managing entries
* Lightweight and easy to extend

## Installation

### 1. Create `.env` file and add code below into it:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lexicon
DB_USERNAME=root
DB_PASSWORD=<your db password>

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
DB_QUEUE=import

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

Set-up your password in this line `DB_PASSWORD=<your db password>`

### 2. Create database with name lexicon
### 3. Run following commands in terminal:
(open terminal in your root project folder)
```bash
composer install
npm install
npm run build
```

### 4. Generate APP_KEY:
```bash
php artisan key:generate
```

### 5. Create database tables if not exists:
```bash
php artisan migrate
```

### 6. Run application:
```bash
composer run dev
```
