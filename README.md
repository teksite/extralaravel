Extra Laravel Package

A powerful Laravel package that enhances your application with additional middleware, validation rules, artisan commands, and configuration utilities.

## Table of Contents

- About
- Author
- Contact
- Installation
- Features
    - Middleware
    - Configuration Files
    - Custom Casts
    - Artisan Commands
    - Validation Rules
- Support

## About

The **Extra Laravel Package** provides a collection of useful tools to streamline development in Laravel. It includes middleware for security, custom validation rules, artisan commands for rapid development, and configuration utilities for localization and more.

## Author

Developed by **Sina Zangiband**.

## Contact

- Website: teksite.net
- Email: support@teksite.net

## Installation

### Step 1: Install via Composer

Run the following command in your terminal:

```bash
composer require teksite/extralaravel
```

### Step 2: Register the Service Provider

#### For Laravel 10 and 11

Add the service provider to the `bootstrap/providers.php` file:

```php
<?php

return [
    // Other providers
    Teksite\Extralaravel\ServiceProvider::class,
];
```

#### For Laravel 5.x and Earlier

Add the service provider to the `config/app.php` file under the `providers` array:

```php
'providers' => [
    // Other Service Providers
    Teksite\Extralaravel\ServiceProvider::class,
],
```

> **Note**: Laravel 5.5 and above supports auto-discovery, so this step is not required for newer versions.

## Features

### Middleware

Enhance your application's security with the included honeypot middleware:

```php
\Teksite\Extralaravel\Middleware\HoneypotMiddleware::class
```

Add it to your `app/Http/Kernel.php` under the `$middleware` or `$routeMiddleware` array as needed.

### Configuration Files

The package provides configuration files for various settings, such as:

- **Area**: Geographic regions
- **Lang**: Language settings
- **Currency**: Currency formats
- **Local-lang**: Localized language settings
- **Mobile-pattern**: Mobile number formats

Access these configurations using:

```php
config('extralaravel.currency');
config('extralaravel.education');
```

### Custom Casts

Simplify data handling with custom casts:

```php
use Teksite\Extralaravel\Casts\SlugCast;
use Teksite\Extralaravel\Casts\JsonCast;
use Teksite\Extralaravel\Casts\IpCast;

protected $casts = [
    'slug' => SlugCast::class,
    'data' => JsonCast::class,
    'ip_address' => IpCast::class,
];
```

### Artisan Commands

Boost productivity with custom artisan commands:

- **API Request Generator**: Create a request class that returns validation, messages, and responses in JSON format.

  ```bash
  php artisan make:request-api <name>
  ```

- **Logic Class Generator**: Separate your HTTP layer from the logical layer.

  ```bash
  php artisan make:logic <name>
  ```

- **Soft Delete Controller**: Generate a controller with soft delete functionality.

  ```bash
  php artisan make:controller-trash <name>
  ```

### Validation Rules

The package includes custom validation rules tailored for specific use cases:

- **Iranian National ID**:

  ```php
  use Teksite\Extralaravel\Rules\CodeMeliRule;
  
  'national_id' => ['required', new CodeMeliRule],
  ```

- **Mobile Number Format**:

  ```php
  use Teksite\Extralaravel\Rules\MobileRule;
  
  'mobile' => ['required', new MobileRule],
  ```

- **Never Pass Rule**: Useful for testing or blocking specific inputs.

  ```php
  use Teksite\Extralaravel\Rules\NeverPassRule;
  
  'input' => ['required', new NeverPassRule],
  ```

- **No HTML**: Prevent HTML tags in input fields.

  ```php
  use Teksite\Extralaravel\Rules\NoHtmlRule;
  
  'content' => ['required', new NoHtmlRule],
  ```

## Support

For questions, issues, or feature requests, please reach out via:

- **Website**: teksite.net
- **Email**: support@teksite.net
- **GitHub Issues**: teksite/extralaravel

Contributions are welcome! Feel free to submit a pull request or open an issue on GitHub.
