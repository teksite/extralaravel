# ExtraLaravel

A collection of reusable utilities, helpers, casts, validation rules, traits, Artisan commands, route macros and Laravel developer tools.

**ExtraLaravel** is designed to provide commonly needed functionality for Laravel applications without introducing unnecessary dependencies or forcing a specific architecture.

---

## Features

- Custom Eloquent Casts
    - IP Address Cast
    - Jalali Date Cast
    - Slug Cast
- Custom Validation Rules
    - Iranian National Code
    - Mobile Number
    - Strong Password
    - No HTML
    - Never Pass
- API Form Request with standardized JSON validation responses
- Phone Verification Trait
- Soft Delete / Trash Management Trait
- Custom Artisan Generator Commands
- Cache Cleanup Command
- Custom Storage Symbolic Links
- Honeypot Middleware
- Recursive Blade Directive
- Useful Global Helpers
- Configurable package behavior
- Route macro for soft-deleted resources

---

## Requirements

- PHP 8.2+
- Laravel 10+
- Laravel 11+
- Laravel 12+

The package is designed to work with modern Laravel applications.
---

## Installation

### Step 1: Install via Composer

Install the package through Composer:

```bash
composer require teksite/extralaravel
```

### Step 2: Register the Service Provider

#### For Laravel > 9

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

---

## Configuration

Publish the package configuration:

```bash
php artisan vendor:publish --provider="Teksite\Extralaravel\ExtraLaravelServiceProvider"
```

Or publish a specific configuration group if you expose individual publish tags.

The package configuration can contain:

```php

return [

    'honeypot' => [
        'enabled' => true,
        'status' => 403,
        'field_name' => env('HONEYPOT_FIELD_NAME', 'honeypot'),
    ],

    'data_path' => '/app/data',

    'custom_links' => [

    ],

];
```

---
## Eloquent Casts

### IP Cast

The `IpCast` automatically uses the request IP address when no value is explicitly provided.

```php
use Teksite\Extralaravel\Casts\IpCast;

protected $casts = [
    'ip' => IpCast::class,
];
```

When creating a model:

```php
$model->ip = null;
$model->save();
```

The current request IP will be stored automatically.

You can also explicitly provide an IP:

```php
$model->ip = '192.168.1.10';
```

---

### Jalali Date Cast

`JalaliDateCast` provides automatic Jalali/Gregorian conversion based on the current application locale.

```php
use Teksite\Extralaravel\Casts\JalaliDateCast;

protected $casts = [
    'published_at' => JalaliDateCast::class,
];
```

When the application locale is:

```php
app()->setLocale('fa');
```

the date is converted to Jalali when reading the model.

When saving a Jalali date, it can be converted back to Gregorian automatically.

The cast expects the following helper functions to be available:

```php
dateToJalali()
dateToGregorian()
```

If these functions are unavailable, the original value is returned.

---

### Slug Cast

The `SlugCast` automatically converts a value into a URL-friendly slug.

```php
use Teksite\Extralaravel\Casts\SlugCast;

protected $casts = [
    'slug' => SlugCast::class,
];
```

Example:

```php
$post->slug = 'My Awesome Post';
$post->save();
```

The stored value becomes:

```text
my-awesome-post
```

---

### Enum Files

The package provides Enum files for various purposes, such as:

- **Areas**: Geographic regions
- **Langs**: Language settings
- **Currencies**: Currency formats
- **LocalLangs**: Localized language settings
- **MobilePatterns**: Mobile number formats



## Validation Rules

### Iranian National Code

Validate an Iranian National Code using:

```php
use Teksite\Extralaravel\Rules\CodeMeliRule;

$request->validate([
    'national_code' => [
        'required',
        new CodeMeliRule(),
    ],
]);
```

The rule validates the 10-digit Iranian national identification code and its check digit.

---

# Mobile Number Rule

The `MobileRule` supports country-specific mobile number validation through the package's `MobilePatterns` enum.

Example:

```php
use Teksite\Extralaravel\Rules\MobileRule;

$request->validate([
    'mobile' => [
        'required',
        new MobileRule(),
    ],
]);
```

By default, Iran is used:

```php
new MobileRule()
```

You can also specify a country:

```php
new MobileRule('iran')
```

Or multiple countries:

```php
new MobileRule([
    'iran',
    'germany',
])
```

The exact supported countries depend on the available `MobilePatterns` enum values.

---

## Password Rule

Validate password strength:

```php
use Teksite\Extralaravel\Rules\PasswordRule;

$request->validate([
    'password' => [
        'required',
        new PasswordRule(),
    ],
]);
```

The rule checks for:

- Lowercase characters
- Uppercase characters
- Numbers
- Special characters
- Minimum password length

---

## No HTML Rule

Prevent HTML tags from being submitted:

```php
use Teksite\Extralaravel\Rules\NoHtmlRule;

$request->validate([
    'name' => [
        'required',
        new NoHtmlRule(),
    ],
]);
```

For example:

```text
John Doe
```

is accepted, while:

```html
<strong>John Doe</strong>
```

is rejected.

> This rule is intended for validation. It should not be considered a replacement for output escaping or XSS protection.

---

## Never Pass Rule

`NeverPassRule` is a rule that always fails validation.

```php
use Teksite\Extralaravel\Rules\NeverPassRule;

$request->validate([
    'field' => [
        new NeverPassRule(),
    ],
]);
```

This can be useful for temporarily disabling fields or testing validation flows.

---

## API Form Request

The package provides:

```php
Teksite\Extralaravel\Http\ApiFormRequest
```

Instead of Laravel's default validation response, validation errors are returned as JSON.

Example:

```php
namespace App\Http\Requests;

use Teksite\Extralaravel\Http\ApiFormRequest;

class StoreUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];
    }
}
```

A validation failure returns a response similar to:

```json
{
    "message": "Validation failed.",
    "errors": {
        "email": [
            "The email field is required."
        ]
    },
    "status": 422,
    "data": []
}
```

Authorization failures return:

```json
{
    "message": "Forbidden.",
    "errors": {
        "auth": [
            "Forbidden You don't have permission"
        ]
    },
    "status": 403,
    "data": []
}
```

---

## Phone Verification

The `MustVerifyPhone` trait provides phone verification functionality similar to Laravel's email verification system.

Add the trait to your User model:

```php
use Teksite\Extralaravel\Traits\MustVerifyPhone;

class User extends Authenticatable
{
    use MustVerifyPhone;

    // ...
}
```

Your model must have:

```text
phone
phone_verified_at
```

columns.

---

### Check Phone Verification

```php
$user->hasVerifiedPhone();
```

Returns:

```php
true
```

or:

```php
false
```

---

### Mark Phone as Verified

```php
$user->markPhoneAsVerified();
```

---

### Mark Phone as Unverified

```php
$user->markPhoneAsUnverified();
```

---

### Get Phone for Verification

```php
$user->getPhoneForVerification();
```

By default, the value of:

```php
$user->phone
```

is returned.

---

### Send Verification Notification

The model using this trait must implement:

```php
public function sendPhoneVerificationNotification(): void
{
    // Send SMS or verification notification
}
```

This allows the package to remain independent of a specific SMS provider.

---

## Trash / Soft Delete Management

The `TrashMethods` trait provides a reusable API for managing soft-deleted models.

Your model must use Laravel's:

```php
Illuminate\Database\Eloquent\SoftDeletes
```

trait.

Example manager:

```php
use Teksite\Extralaravel\Traits\TrashMethods;

class UserTrashManager
{
    use TrashMethods;

    protected function getModelClass(): string
    {
        return User::class;
    }
}
```

---

### Count Trashed Records

```php
$manager->trashCount();
```

---

### Get Trashed Records

```php
$manager->getTrashes();
```

Custom pagination:

```php
$manager->getTrashes(
    perPage: 50
);
```

---

### Restore Records

Restore one record:

```php
$manager->restoreOne(10);
```

Restore multiple records:

```php
$manager->restore([10, 20, 30]);
```

---

### Restore All

```php
$manager->restoreAll();
```

---

### Permanently Delete Records

Permanently delete one:

```php
$manager->wipeOne(10);
```

Multiple records:

```php
$manager->wipe([10, 20, 30]);
```

---

### Permanently Delete Everything in Trash

```php
$manager->wipeAll();
```

> `wipe()` and `wipeAll()` use Laravel's `forceDelete()` and permanently remove records.

---

## Trash Resource Routes

ExtraLaravel provides a `trashResource()` route macro for creating standardized soft-delete routes.

Example:

```php
use App\Http\Controllers\UserController;

Route::trashResource(
    'users',
    UserController::class
);
```

This can generate routes for:

| Action | Method | URI |
|---|---|---|
| Index | GET | `/users/trash` |
| Reinstate | PATCH | `/users/trash/{id}` |
| Prune | DELETE | `/users/trash/{id}` |
| Restore | PATCH | `/users/trash` |
| Flush | DELETE | `/users/trash` |

The controller should provide the corresponding methods:

```php
public function index()
{
    //
}

public function reinstate($id)
{
    //
}

public function prune($id)
{
    //
}

public function restore()
{
    //
}

public function flush()
{
    //
}
```

---

## Route Middleware

Middleware can be supplied through the options array:

```php
Route::trashResource(
    'users',
    UserController::class,
    [
        'middleware' => ['auth'],
    ]
);
```

---

### Custom Prefix

```php
Route::trashResource(
    'users',
    UserController::class,
    [
        'prefix' => 'admin',
    ]
);
```

---

### Custom Route Name Prefix

```php
Route::trashResource(
    'users',
    UserController::class,
    [
        'as' => 'admin.users',
    ]
);
```

---

### Only Specific Actions

```php
Route::trashResource(
    'users',
    UserController::class,
    [
        'only' => [
            'index',
            'restore',
        ],
    ]
);
```

---

### Exclude Actions

```php
Route::trashResource(
    'users',
    UserController::class,
    [
        'except' => [
            'flush',
        ],
    ]
);
```

---

## Artisan Commands

ExtraLaravel provides several custom Artisan commands.

---

### API Request Generator

Create an API Form Request:

```bash
php artisan make:request-api StoreUserRequest
```

Example nested request:

```bash
php artisan make:request-api Admin/StoreUserRequest
```

The generated class extends:

```php
Teksite\Extralaravel\Http\ApiFormRequest
```

---

## Logic Generator

Create a Logic class:

```bash
php artisan make:logic UserLogic
```

Nested class:

```bash
php artisan make:logic User/UserLogic
```

Logic classes can be used for database queries and application-specific business/query logic.

---

## Trash Controller Generator

Create a soft-delete controller:

```bash
php artisan make:controller-trash UserController
```

For an API controller:

```bash
php artisan make:controller-trash UserController --api
```

---

## Cache Purge Command

ExtraLaravel provides:

```bash
php artisan cache:purge
```

The command can clean:

- Expired database cache
- Expired file cache
- Expired Sanctum tokens

---

### Dry Run

To inspect what would be deleted without deleting anything:

```bash
php artisan cache:purge --dry-run
```

---

### Force Production Execution

By default, destructive execution in production asks for confirmation.

To force execution:

```bash
php artisan cache:purge --force
```

---

### Batch Size

Default batch size:

```text
1000
```

Custom:

```bash
php artisan cache:purge --limit=500
```

---

## Skip Database Cache

```bash
php artisan cache:purge --skip-database
```

---

### Skip File Cache

```bash
php artisan cache:purge --skip-files
```

---

### Skip Sanctum Tokens

```bash
php artisan cache:purge --skip-tokens
```

---

### Custom Database Connection

```bash
php artisan cache:purge --connection=mysql
```

---

## Custom Storage Links

The package provides:

```bash
php artisan storage:link-custom
```

This command creates symbolic links defined in the package configuration.

Example:

```php
'custom_links' => [

    'uploads' => [
        'link' => public_path('uploads'),
        'target' => storage_path('app/uploads'),
    ],

],
```

Then:

```bash
php artisan storage:link-custom
```

---

### Force Recreate Links

```bash
php artisan storage:link-custom --force
```

---

### Relative Links

```bash
php artisan storage:link-custom --relative
```

---

### Verify Links

```bash
php artisan storage:link-custom --verify
```

Options can be combined:

```bash
php artisan storage:link-custom --force --relative --verify
```

---

# Honeypot Middleware

ExtraLaravel provides a simple honeypot middleware to detect automated form submissions.

Configuration:

```php
'honeypot' => [

    'enabled' => true,

    'status' => 403,

    'field_name' => env(
        'HONEYPOT_FIELD_NAME',
        'honeypot'
    ),

],
```

Register the middleware in your application according to your Laravel version and apply it to the routes/forms you want to protect.

Example form field:

```html
<input
    type="text"
    name="honeypot"
    tabindex="-1"
    autocomplete="off"
>
```

The field should normally remain empty.

If a request submits a non-empty honeypot field, the middleware rejects the request.

---

## Blade Recursive Directive

ExtraLaravel provides a recursive Blade directive.

Example:

```blade
@recursive($items, $item)
    {{ $item }}
@endrecursive
```

It can be used for recursively rendering nested arrays or traversable data.

For arrays stored in variables that may need casting:

```blade
@recursive((array)$items, $item)
    {{ $item }}
@endrecursive
```

---

## Helper Functions

ExtraLaravel provides several global helper functions.

---

### changeToSlug

Convert text to a slug:

```php
changeToSlug('My Awesome Post');
```

Result:

```text
my-awesome-post
```

Custom separator:

```php
changeToSlug(
    'My Awesome Post',
    '_'
);
```

---

### toEnglishNumber

Convert Persian and Arabic digits to English digits:

```php
toEnglishNumber('۱۲۳۴۵');
```

Result:

```text
12345
```

Arabic digits are also supported:

```php
toEnglishNumber('١٢٣٤٥');
```

---

### normalizePersianText

Normalize common Arabic characters to their Persian equivalents:

```php
normalizePersianText('ي ك ة');
```

The helper also removes Arabic diacritics.

---

### convertSeconds

Convert seconds to:

```text
HH:MM:SS
```

Example:

```php
convertSeconds(3661);
```

Result:

```text
01:01:01
```

You can request an array:

```php
convertSeconds(
    3661,
    'array'
);
```

Result:

```php
[
    'hours' => 1,
    'minutes' => 1,
    'seconds' => 1,
]
```

---

### currentUrlWithoutQueries

Get the current URL without query parameters:

```php
currentUrlWithoutQueries();
```

---

### exploding

Split a string using both English and Persian commas:

```php
exploding('one,two،three');
```

Returns a Laravel Collection.

---

### var_export_short

A shorter array representation of `var_export()`:

```php
var_export_short($data);
```

To return the generated string:

```php
$output = var_export_short(
    $data,
    true
);
```

---

### arrayToDot

Convert Laravel-style array notation to dot notation:

```php
arrayToDot('users[0][name]');
```

Result:

```text
users.0.name
```

---

### dotToArray

Convert dot notation to array-style notation:

```php
dotToArray('users.0.name');
```

Result:

```text
users][0][name]
```

---

### normalizePath

Normalize filesystem paths:

```php
normalizePath(
    'foo/bar\\baz'
);
```

---

### is_rtl

Determine whether a language is RTL:

```php
is_rtl('fa');
```

Result:

```php
true
```

If no language is provided, the current application locale is used:

```php
is_rtl();
```

---

### get_error

Retrieve validation errors using array-style field names:

```php
get_error(
    $errors,
    'users[0][email]'
);
```

---

### getStaticData

Read static data from PHP files using dot notation.

Example directory:

```text
app/
├── data/
│   └── times/
│       └── canada/
│           └── torentu.php
```

If `tehran.php` contains:

```php
<?php

return [
    'timezone' => 'Asia/Tehran',
];
```

You can retrieve it using:

```php
getStaticData(
    'app.data.times.iran.tehran.timezone'
);
```

Result:

```text
Asia/Tehran
```

A default value can be provided:

```php
getStaticData(
    'app.data.unknown',
    'default value'
);
```

---

## Suggested Project Structure

A typical application using ExtraLaravel can organize its code as:

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
│
├── Logics/
│
├── Models/
│
└── ...
```

Generated classes can then follow a predictable structure:

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── UserController.php
│   │
│   └── Requests/
│       └── StoreUserRequest.php
│
└── Logics/
    └── UserLogic.php
```

---

## Design Philosophy

ExtraLaravel intentionally focuses on small, reusable Laravel components rather than providing a large framework on top of Laravel.

The package follows several principles:

- Prefer Laravel's native functionality where possible.
- Keep utilities independent from application-specific business logic.
- Avoid unnecessary dependencies.
- Make functionality configurable.
- Provide reusable Artisan generators.
- Keep API responses predictable.
- Keep validation rules independent and composable.
- Support Persian/Jalali-oriented Laravel applications without forcing them on other applications.

---

## Security Notes

ExtraLaravel does not replace Laravel's built-in security mechanisms.

In particular:

- Always use Laravel's authorization system.
- Always escape user-controlled output.
- Do not rely on `NoHtmlRule` as your only XSS protection.
- Validate and sanitize uploaded files independently.
- Protect destructive commands in production.
- Restrict trash management routes using proper authentication and authorization.
- Do not expose permanent-delete endpoints without authorization.
- Configure honeypot fields correctly.

---

# Contributing

Contributions are welcome.

Before submitting a pull request:

1. Write or update tests.
2. Follow Laravel coding conventions.
3. Run the test suite.
4. Run static analysis if configured.
5. Run the code formatter.
6. Keep changes focused and backwards compatible where possible.

---

# License

This package is open-sourced software licensed under the MIT license.

See the `LICENSE` file for more information.

---

# Roadmap

Possible future additions:

- More Laravel-specific validation rules
- Additional Eloquent casts
- More reusable traits
- Additional Artisan generators
- Improved API response helpers
- More storage utilities
- Queue-related utilities
- Additional Persian/Jalali helpers
- Expanded automated test coverage


# Author

Developed by **Sina Zangiband**.

## Contact

- Website:
    - [teksite.net](https://teksite.net)
    - [laratek.net](https://laratek.net)
    - [laratek.ir](https://laratek.ir)
-
- Email: [sina.zangiband@gmail.com](mailto:sina.zangiband@gmail.com)
