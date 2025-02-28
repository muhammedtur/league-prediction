<h1 align="center">
  League Championship Prediction
</h1>

<p align="center"><a href="https://github.com/muhammedtur/mobile-api/releases" target="_blank"><img src="https://img.shields.io/badge/version-v1.0-blue?style=for-the-badge&logo=none" alt="cli version" /></a>&nbsp;</p>

## Requirements

```bash
#Laravel Framework
Laravel 12

# Composer
Composer version 2.8.6 2025-02-25 13:03:50

# PHP
PHP 8.3.16 (cli) (built: Jan 14 2025 20:07:09) (NTS Visual C++ 2019 x64)

#Database
PHP sqlite3 extension
```

## Installation Guide

```bash
# Clone the project
git clone https://github.com/muhammedtur/league-prediction.git

# Change the directory
cd league-prediction
```

To install the dependencies:

```bash
composer update

env.example -> .env (Should be updated database variables and APP_URL)

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

php artisan key:generate

php artisan migrate

php artisan db:seed
```

## Running

To run the app:

```bash
php artisan serve --port=80
```

## Usage

```bash
Default URL for "Insider League": http://127.0.0.1/league/insider

