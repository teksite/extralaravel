
# Extra laravel Package

## About
This package is developed to add some useful features to laravel such as middleware, rules, commands and more.

### Author
Sina Zangiband

### Contact
- Website: [laratek.ir](https://laratek.ir)
- Alternate Website: [teksite.net](https://teksite.net)

---

## Installation

### Step 1: Install via Composer
Run the following command in your CLI:

```bash
composer require teksite\extralaravel
```

### Step 2: Register the Service Provider

#### Laravel 10 and 11
Add the following line to the `bootstrap/providers` file:

```php
Teksite\Extralaravel\ServiceProvider::class,
```

#### Laravel 5.x and earlier
If you are using Laravel 5.x or earlier, register the service provider in the `config/app.php` file under the `providers` array:

```php
'providers' => [
    // Other Service Providers
    Teksite\Handler\ServiceProvider::class,
];
```

> **Note:** This step is not required for newer versions of Laravel (5.x and above).

---

Feel free to reach out if you have any questions or need assistance with this package!
