# ExtraLaravel

A collection of reusable utilities for Laravel applications: Eloquent casts, validation rules, an API form request,
traits, route macros, Artisan generators and maintenance commands, a honeypot middleware, a recursive Blade directive,
ready-to-use enums and global helper functions.

ExtraLaravel focuses on small, independent building blocks. Nothing is forced on your application: every piece can
be used on its own, and the package does not impose an architecture.

---

## Table of contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Eloquent casts](#eloquent-casts)
- [Enums](#enums)
- [Validation rules](#validation-rules)
- [API form request](#api-form-request)
- [Phone verification trait](#phone-verification-trait)
- [Trash (soft delete) management](#trash-soft-delete-management)
- [Trash resource routes](#trash-resource-routes)
- [Artisan commands](#artisan-commands)
- [Honeypot](#honeypot)
- [Blade directives](#blade-directives)
- [Helper functions](#helper-functions)
- [Translations](#translations)
- [Octane and performance notes](#octane-and-performance-notes)
- [Upgrade notes](#upgrade-notes)
- [Testing](#testing)
- [Security notes](#security-notes)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## Requirements

| Requirement      | Version                                                                       |
|------------------|-------------------------------------------------------------------------------|
| PHP              | 8.3 or newer                                                                  |
| Laravel          | 12.x or 13.x                                                                  |
| `morilog/jalali` | ^3.5 (installed automatically, used by `JalaliDateCast`)                      |
| `laravel/sanctum`| Optional. Only needed for the expired-token step of `cache:purge`.            |

The test suite runs on PHP 8.3 and 8.4 against Laravel 12 and 13 (Orchestra Testbench 10 and 11).

---

## Installation

```bash
composer require teksite/extralaravel
```

The service provider is registered through Laravel's package auto-discovery, so no manual step is required.

If your application disables package discovery, register it yourself in `bootstrap/providers.php`:

```php
return [
    // ...
    Teksite\Extralaravel\ExtraLaravelServiceProvider::class,
];
```

The global helper functions (`changeToSlug()`, `getStaticData()`, ...) are loaded automatically through Composer's
`autoload.files`.

---

## Configuration

The package works without publishing anything: its defaults are merged into your configuration automatically.
To customize them, publish the config file:

```bash
php artisan vendor:publish --tag=extralaravel-config
```

Available publish tags:

| Tag                    | What it publishes                                          |
|------------------------|------------------------------------------------------------|
| `extralaravel`         | Everything below                                           |
| `extralaravel-config`  | `config/extralaravel.php`                                  |
| `extralaravel-stubs`   | Generator stubs to `stubs/extralaravel/`                   |

The default configuration:

```php
return [

    // Hidden field used by the honeypot middleware and the @honeypot directive.
    'honeypot' => [
        'enabled'    => true,
        'status'     => 403, // must be a 4xx/5xx code, otherwise 403 is used
        'field_name' => env('HONEYPOT_FIELD_NAME', 'honeypot'),
    ],

    // Application locales in which JalaliDateCast converts between Gregorian and Jalali dates.
    'jalali_date_locales' => ['fa'],

    // Locales treated as right-to-left by is_rtl().
    'rtl_locales' => ['ar', 'fa', 'he', 'ur', 'ps', 'ku', 'ckb', 'sd', 'yi', 'dv', 'ug'],

    // Length limits used by PasswordRule when no explicit values are passed to its constructor.
    'password' => [
        'min_length' => 8,
        'max_length' => 128,
    ],

    // Directory (relative to base_path()) that getStaticData() may read PHP files from.
    'data_path' => 'app/data',
];
```

---

## Eloquent casts

### `IpCast`

Stores an IP address and validates it.

```php
use Teksite\Extralaravel\Casts\IpCast;

protected $casts = [
    'ip' => IpCast::class,
];
```

- When the attribute is set to `null`, the IP of the current request (`request()->ip()`) is stored.
- Any other value must be a valid IPv4 or IPv6 address, otherwise an `InvalidArgumentException` is thrown. Arbitrary
  text is never stored.

```php
$model->ip = null;            // stores the current request IP
$model->ip = '192.168.1.10';  // stored as given
$model->ip = 'not-an-ip';     // throws InvalidArgumentException
```

### `JalaliDateCast`

Converts between the Gregorian calendar (stored in the database) and the Jalali calendar (shown to users),
using [`morilog/jalali`](https://github.com/morilog/jalali).

```php
use Teksite\Extralaravel\Casts\JalaliDateCast;

protected $casts = [
    'published_at' => JalaliDateCast::class,
];
```

The conversion is active only when the application locale is listed in `extralaravel.jalali_date_locales`
(`['fa']` by default). In every other locale the value passes through unchanged.

```php
app()->setLocale('fa');

$post->published_at;                       // "1403/05/10 14:30:00"  (date-only columns: "1403/05/10")

$post->published_at = '1403/05/10 14:30';  // saved as "2024-07-31 14:30:00"
$post->published_at = '۱۴۰۳/۰۵/۱۰';        // Persian digits are accepted, saved as "2024-07-31"
```

Details:

- On write, only strings that look like a Jalali date (`YYYY/MM/DD` or `YYYY-MM-DD`, optional `HH:MM[:SS]`, year
  below 1700) are converted. Other strings and `DateTimeInterface` instances are stored as they are.
- An impossible Jalali date throws an `InvalidArgumentException`.
- Setting the attribute to `null` stores the current time (`now()`), not `NULL`. Do not use this cast on nullable
  columns where `NULL` must be preserved.

### `SlugCast`

Turns a string into a URL-friendly slug when the attribute is set.

```php
use Teksite\Extralaravel\Casts\SlugCast;

protected $casts = [
    'slug' => SlugCast::class,
];
```

```php
$post->slug = 'My Awesome Post';   // "my-awesome-post"
$post->slug = 'MyPost';            // "my-post"   (camelCase is split)
$post->slug = 'HTMLParser';        // "html-parser"
$post->slug = null;                // null
$post->slug = 123;                 // throws InvalidArgumentException (must be a string)
```

Persian text is kept intact. The same logic is available as the `changeToSlug()` helper.

---

## Enums

All enums live in `Teksite\Extralaravel\Enums`.

| Enum            | Cases | Backed value | Example                                      |
|-----------------|-------|--------------|----------------------------------------------|
| `Areas`         | 252   | country name | `Areas::IR->value` is `"Iran"`               |
| `Currencies`    | 168   | currency name| `Currencies::USD->value` is `"US Dollar"`    |
| `Langs`         | 142   | language name| `Langs::fa->value` is `"Persian - فارسی"`    |
| `LocalLangs`    | 434   | locale name  | `LocalLangs::fa_IR->value` is `"Persian (Iran)"` |
| `Educations`    | 6     | degree name  | `Educations::BACHELOR->value` is `"bachelor's degree"` |
| `TimeZones`     | 400   | (not backed) | `TimeZones::Asia_Tehran`                     |
| `MobilePatterns`| 88    | (not backed) | `MobilePatterns::IRAN`                       |

The backed enums are regular PHP enums, so `from()`, `tryFrom()` and `cases()` work as usual:

```php
use Teksite\Extralaravel\Enums\Areas;

Areas::tryFrom('Iran');                       // Areas::IR
collect(Areas::cases())->pluck('value', 'name'); // ['IR' => 'Iran', ...] for a select box
```

### `TimeZones`

Static offset table for every supported zone. Case names use underscores instead of slashes
(`Asia_Tehran` is `Asia/Tehran`).

```php
use Teksite\Extralaravel\Enums\TimeZones;

TimeZones::allZones();                         // ['Asia/Tehran', ...]
TimeZones::allTimes();                         // unique offsets, e.g. ['+3:30', ...]

TimeZones::getOffset('Asia/Tehran');           // "+3:30"   (standard offset, never changes during the year)
TimeZones::getOffset(TimeZones::Asia_Tehran);  // "+3:30"
TimeZones::getOffset('Mars/Base');             // null

// Daylight-saving aware, computed from PHP's timezone database:
TimeZones::getCurrentOffset('America/Los_Angeles');                          // "-7:00" in summer, "-8:00" in winter
TimeZones::getCurrentOffset('America/Los_Angeles', new DateTimeImmutable('2026-01-15')); // "-8:00"
```

### `MobilePatterns`

Mobile number rules for 88 countries. It powers `MobileRule`, but is also useful on its own. Patterns for the first
ten countries (Iran, USA, Canada, United Kingdom, Germany, France, Turkey, UAE, India, Australia) are
intentionally lenient; the other patterns are derived from Google's
[libphonenumber](https://github.com/google/libphonenumber) mobile ranges. Treat a match as "looks like a mobile
number of this country", not as proof that the number is assigned to a subscriber.

Accepted input formats (after normalization): the number with an international prefix (`+989123456789`,
`00989123456789`), with the national trunk prefix (`09123456789`) or without any prefix (`9123456789`).
Persian/Arabic digits, spaces and punctuation are handled automatically. A bare digit string that starts with the
dial code but has no `+` or `00` (for example `989123456789`) is **not** accepted.

```php
use Teksite\Extralaravel\Enums\MobilePatterns;

MobilePatterns::IRAN->validate('۰۹۱۲ ۳۴۵ ۶۷۸۹');   // true
MobilePatterns::IRAN->validate('+98 912 345 6789'); // true
MobilePatterns::IRAN->validate('abc');              // false
```

**Instance methods**

| Method                         | Description                                                                     |
|--------------------------------|---------------------------------------------------------------------------------|
| `code()`                       | Dial code without `+` (`"98"`). USA/Canada share `"1"`, Russia/Kazakhstan `"7"`.|
| `iso()`                        | ISO 3166-1 alpha-2 code (`"IR"`).                                               |
| `label()`                      | English country name.                                                           |
| `flag()`                       | Flag emoji. Some platforms (e.g. Windows) render the two letters instead.       |
| `nationalPrefix()`             | Trunk prefix (`"0"`, `"8"`, `"06"`) or `""` when the country has none.          |
| `pattern()`                    | The regular expression.                                                         |
| `example($international)`      | A valid sample number, national (`09123456789`) or international (`+98...`).    |
| `validate($phone)`             | Normalizes the input, then tests it.                                            |
| `matches($normalizedPhone)`    | Tests an already normalized number (normalize once when checking many countries).|
| `nationalNumber($phone)`       | Number without international or trunk prefix, or `null` when invalid.           |
| `toNational($phone)`           | Trunk prefix + national number (`"09123456789"`), or `null`.                    |
| `toE164($phone)`               | `"+989123456789"`, or `null`.                                                   |

**Static methods**

| Method                                   | Description                                                                |
|------------------------------------------|----------------------------------------------------------------------------|
| `normalize($phone)`                      | Digits to English, everything except digits and `+` removed, `00` becomes `+`.|
| `country($name)`                         | Find by case name, English label, ISO code or the `UK` alias. Returns `null` if unknown.|
| `fromIso($iso)`                          | Find by ISO code (`"ir"`, `"GB"`, `"UK"`).                                 |
| `fromDialCode($code)`                    | List of countries using a dial code (`"98"`, `"+98"`, `"0098"`, `98`).     |
| `validateWithCountry($phone, $country)`  | Validate against one country (enum or name).                               |
| `validateAny($phone, $countries = null)` | Validate against several countries, or against all of them.                |
| `detectCountry($phone)`                  | First matching country in declaration order.                               |
| `detectCountries($phone)`                | Every matching country.                                                    |
| `canonical($phone, $country = null)`     | Digits-only international form (`"989123456789"`).                         |
| `e164($phone, $country = null)`          | Same with a leading `+`.                                                   |
| `areSame($a, $b, $country = null)`       | Whether two numbers are the same subscriber, however written.              |
| `options($sortByLabel = false)`          | `['IR' => 'Iran', ...]` for select boxes.                                  |
| `mask($phone, $visibleStart = 4, $visibleEnd = 3)` | Hide the middle of a number for logs: `+989******789`.           |

```php
MobilePatterns::country('uk');                         // MobilePatterns::UNITED_KINGDOM
MobilePatterns::fromDialCode('+1');                    // [USA, CANADA]
MobilePatterns::detectCountry('+447400123456');        // MobilePatterns::UNITED_KINGDOM
MobilePatterns::detectCountries('+12015550123');       // [USA, CANADA]

MobilePatterns::canonical('09123456789', 'iran');      // "989123456789"
MobilePatterns::e164('09123456789', 'iran');           // "+989123456789"
MobilePatterns::areSame('0912 345 6789', '+98 912 345 6789', 'iran'); // true

MobilePatterns::mask('+989123456789');                 // "+989******789"
MobilePatterns::options(sortByLabel: true);            // ['AF' => 'Afghanistan', ...]
MobilePatterns::IRAN->example(true);                   // "+989123456789"
MobilePatterns::IRAN->flag();                          // "🇮🇷"
```

Notes:

- Numbers without any prefix can match several countries. Use `+` numbers (or pass the country explicitly) when you
  need a reliable result. `detectCountry()` returns the first match in declaration order, which is why the first ten
  cases keep their original order.
- Inputs longer than 20 characters after normalization are rejected before any regular expression runs.
- Mobile ranges change over time; refresh the patterns from libphonenumber periodically.

---

## Validation rules

All rules implement Laravel's `ValidationRule` contract and can be used in arrays, `Rule` lists and `FormRequest`s.
Failure messages are translatable, see [Translations](#translations).

### `CodeMeliRule`

Validates an Iranian national code (10 digits and check digit). Persian and Arabic digits are accepted, integers are
left-padded with zeros, and codes made of a single repeated digit (`1111111111`) are rejected.

```php
use Teksite\Extralaravel\Rules\CodeMeliRule;

$request->validate([
    'national_code' => ['required', new CodeMeliRule()],
]);
```

### `MobileRule`

Country-specific mobile validation built on [`MobilePatterns`](#mobilepatterns). Iran is the default.

```php
use Teksite\Extralaravel\Enums\MobilePatterns;
use Teksite\Extralaravel\Rules\MobileRule;

new MobileRule();                                             // Iran
new MobileRule('germany');                                    // by name
new MobileRule('DE');                                         // by ISO code
new MobileRule(MobilePatterns::TURKEY);                       // by enum
new MobileRule(['iran', MobilePatterns::USA, 'iraq']);        // any of several countries
```

- An unknown country name throws `InvalidArgumentException` when the rule is constructed.
- Input that is not a string, is empty, or is longer than 32 characters fails immediately and is never normalized.
- To customize the generic failure message, define the `validation.mobile` translation key in your application.

### `PasswordRule`

Requires a lowercase letter, an uppercase letter, a number and a symbol, within a length range. Every failed
requirement is reported separately. Any character that is neither a letter nor a digit (in any script) counts as a
symbol, so Persian letters are not symbols.

```php
use Teksite\Extralaravel\Rules\PasswordRule;

new PasswordRule();          // limits from config: 8 to 128 characters
new PasswordRule(10);        // minimum 10
new PasswordRule(10, 64);    // minimum 10, maximum 64
```

The maximum length protects your hasher from extremely long inputs.

### `NoHtmlRule`

Fails when the value contains HTML tags (`strip_tags($value) !== $value`). Non-string values are ignored.

```php
use Teksite\Extralaravel\Rules\NoHtmlRule;

'name' => ['required', new NoHtmlRule()],
```

> This is input validation, not XSS protection. Always escape output.

### `NeverPassRule`

Always fails. Useful for disabling a field temporarily or for testing validation flows.

```php
use Teksite\Extralaravel\Rules\NeverPassRule;

'field' => [new NeverPassRule()],
```

---

## API form request

`ApiFormRequest` returns validation and authorization failures as JSON instead of redirecting or rendering HTML.

```php
namespace App\Http\Requests;

use Teksite\Extralaravel\Http\ApiFormRequest;

class StoreUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string'],
            'email' => ['required', 'email'],
        ];
    }
}
```

Validation failure (HTTP 422):

```json
{
    "message": "Validation failed.",
    "errors": {
        "email": ["The email field is required."]
    },
    "status": 422,
    "data": []
}
```

Authorization failure (HTTP 403):

```json
{
    "message": "Forbidden.",
    "errors": {
        "auth": "Forbidden. You don't have permission"
    },
    "status": 403,
    "data": []
}
```

Generate a request class with [`make:request-api`](#api-request-generator).

---

## Phone verification trait

`MustVerifyPhone` mirrors Laravel's email verification helpers for phone numbers.

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Teksite\Extralaravel\Traits\MustVerifyPhone;

class User extends Authenticatable
{
    use MustVerifyPhone;

    protected function casts(): array
    {
        return ['phone_verified_at' => 'datetime'];
    }

    // Required: the package does not depend on any SMS provider.
    public function sendPhoneVerificationNotification(): void
    {
        // Send an SMS or notification here.
    }
}
```

The model needs the `phone` and `phone_verified_at` columns.

| Method                              | Description                                                      |
|-------------------------------------|------------------------------------------------------------------|
| `hasVerifiedPhone(): bool`          | `true` when `phone_verified_at` is not null.                     |
| `markPhoneAsVerified(): bool`       | Sets `phone_verified_at` to now and saves.                       |
| `markPhoneAsUnverified(): bool`     | Clears `phone_verified_at` and saves.                            |
| `getPhoneForVerification(): string` | Returns `$this->phone` as a string.                              |
| `sendPhoneVerificationNotification()` | Abstract. You implement it.                                    |

---

## Trash (soft delete) management

`TrashMethods` gives a service class a complete API for working with the soft-deleted records of one model. The model
must use Laravel's `SoftDeletes` trait; otherwise a `LogicException` is thrown.

```php
use App\Models\User;
use Teksite\Extralaravel\Traits\TrashMethods;

class UserTrashManager
{
    use TrashMethods;

    protected function getModelClass(): string
    {
        return User::class;
    }
}

$manager = new UserTrashManager();
```

| Method                                   | Description                                                            |
|------------------------------------------|------------------------------------------------------------------------|
| `trashCount(): int`                      | Number of trashed records.                                             |
| `getTrashes(...)`                        | Paginated trashed records (see below).                                 |
| `restore($ids)` / `restoreOne($id)`      | Restore records. Returns the number of restored records.               |
| `restoreAll()`                           | Restore everything in the trash.                                       |
| `wipe($ids)` / `wipeOne($id)`            | Permanently delete (`forceDelete`) trashed records.                    |
| `wipeAll()`                              | Permanently delete everything in the trash.                            |
| `restoreWithEvents($ids)`, `restoreOneWithEvents($id)`, `restoreAllWithEvents()` | Same as above, but record by record so model events fire (`restoring`, `restored`). |
| `wipeWithEvents($ids)`, `wipeOneWithEvents($id)`, `wipeAllWithEvents()`          | Same, firing `forceDeleting` / `forceDeleted`. |

```php
$manager->getTrashes(perPage: 50);
$manager->getTrashes(perPage: 20, columns: ['id', 'name'], page: 2);

$manager->restoreOne(10);
$manager->restore([10, 20, 30]);
$manager->wipeAllWithEvents();
```

Behavior worth knowing:

- Only trashed records are ever touched. Passing the ID of an active record changes nothing and is not counted.
- `null` and empty-string IDs are ignored.
- The plain variants (`restore`, `wipe`, ...) run a single bulk query and do **not** fire model events. Use the
  `*WithEvents` variants when observers or listeners must run; they process records in chunks.
- Large ID lists are split into chunks (1000 by default) so they cannot exceed a database driver's bound-parameter
  limit.
- `getTrashes()` caps the page size at 250 (and never goes below 1), so a client-supplied `per_page` cannot request the
  whole table. Still validate request input before passing it in.

Override these protected hooks to customize the behavior:

```php
protected function trashChunkSize(): int { return 500; }     // default 1000
protected function trashMaxPerPage(): int { return 100; }    // default 250
protected function trashedQuery(): Builder { /* add scopes, e.g. tenant filters */ }
```

---

## Trash resource routes

`Route::trashResource()` registers five standard routes for a soft-deletable resource.

```php
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::trashResource('users', UserController::class);
```

| Action      | Method | URI                  | Route name             | Controller method |
|-------------|--------|----------------------|------------------------|-------------------|
| `index`     | GET    | `/users/trash`       | `users.trash.index`    | `index()`         |
| `reinstate` | PATCH  | `/users/trash/{user}`| `users.trash.reinstate`| `reinstate($id)`  |
| `prune`     | DELETE | `/users/trash/{user}`| `users.trash.prune`    | `prune($id)`      |
| `restore`   | PATCH  | `/users/trash`       | `users.trash.restore`  | `restore()`       |
| `flush`     | DELETE | `/users/trash`       | `users.trash.flush`    | `flush()`         |

The wildcard is the singular form of the resource (`users` becomes `{user}`). `reinstate` and `prune` load the model
with `withTrashed()` and are protected by `TrashOnlyBindingMiddleware`, which answers **404** when:

- the target record is **not** soft-deleted, or
- a parent record of a nested resource **is** soft-deleted.

This means `PATCH /users/trash/5` can never act on an active user. Model binding must run (the `web`/`api` middleware
groups include `SubstituteBindings`). Scaffold the matching controller with
[`make:controller-trash`](#trash-controller-generator).

### Nested resources

```php
Route::trashResource('users.posts', PostController::class);
// GET    users/{user}/posts/trash
// PATCH  users/{user}/posts/trash/{post}   (name: users.posts.trash.reinstate)
```

### Options

Pass options as the third argument or chain them fluently. Both produce the same routes.

```php
Route::trashResource('users', UserController::class, [
    'prefix'     => 'admin',
    'middleware' => ['auth', 'can:manage-trash'],
    'as'         => 'admin',            // route names become admin.users.trash.index, ...
    'only'       => ['index', 'restore'],
]);

Route::trashResource('users', UserController::class)
    ->prefix('admin')
    ->middleware(['auth', 'can:manage-trash'])
    ->as('admin')
    ->except(['flush']);
```

| Option                  | Description                                                                         |
|-------------------------|-------------------------------------------------------------------------------------|
| `prefix`                | URI prefix.                                                                         |
| `middleware`            | String or array of middleware applied to all generated routes.                      |
| `as`                    | Route name prefix; names are `{as}.{resource}.trash.{action}`.                      |
| `only` / `except`       | Limit the actions. Unknown action names throw `InvalidArgumentException`; using the same action in both throws too. |
| `where`                 | Route parameter constraints, e.g. `['user' => '[0-9]+']`.                           |
| `parameters`            | Rename wildcards, e.g. `['users' => 'account']` gives `{account}`.                  |
| `names`                 | Custom route name per action, e.g. `['index' => 'trash.list']`.                     |
| `scoped` / `scopeBindings` | Scoped route-model binding for nested resources.                                 |
| `bindingFields`         | Custom binding columns, e.g. `['user' => 'uuid']`.                                  |
| `shallow`               | Use shallow URIs for single-record actions of nested resources.                     |

Register several resources at once with `Route::trashResources(['users' => UserController::class, ...], $options)`.

> Restrict these routes with authentication and authorization. `prune` and `flush` delete data permanently.

---

## Artisan commands

Commands are registered only when the application runs in the console.

### API request generator

```bash
php artisan make:request-api StoreUserRequest
php artisan make:request-api Admin/StoreUserRequest
```

Creates a class in `app/Http/Requests` that extends [`ApiFormRequest`](#api-form-request).

### Logic generator

```bash
php artisan make:logic UserLogic
php artisan make:logic User/UserLogic
```

Creates an empty class in `app/Logics`, handy for query and business logic that does not belong in a controller or model.

### Service generator

```bash
php artisan make:service BillingService
```

Creates a class in `app/Services` with an `execute(): void` method.

### Trash controller generator

```bash
php artisan make:controller-trash UserController          # redirect-style controller
php artisan make:controller-trash UserController --api    # JSON controller
```

Creates a controller in `app/Http/Controllers` with the five methods expected by
[`Route::trashResource()`](#trash-resource-routes).

All generators accept `--force` (`-f`) to overwrite an existing class. Without it, an existing class is never
overwritten.

### Customizing stubs

```bash
php artisan vendor:publish --tag=extralaravel-stubs
```

Stubs are copied to `stubs/extralaravel/`. When a file with the same name exists there, it is used instead of the
package stub. Available stubs: `api-request.stub`, `logic-class.stub`, `service-class.stub`,
`trash-resource-controller.stub`, `trash-resource-controller-api.stub`. Placeholders: `{{ namespace }}`,
`{{ rootNamespace }}`, `{{ class }}`.

### `cache:purge`

Removes expired cache data:

- expired rows of a database cache store, and expired rows of its cache lock table
- expired files of the file cache (empty directories are removed afterwards)
- expired Sanctum tokens (through `sanctum:prune-expired`, when Sanctum is installed)

```bash
php artisan cache:purge
php artisan cache:purge --dry-run          # show what would be deleted, delete nothing
php artisan cache:purge --force            # skip the production confirmation
php artisan cache:purge --limit=500        # batch size (default 1000)
php artisan cache:purge --store=database   # database cache store name (default "database")
php artisan cache:purge --connection=mysql # override the database connection
php artisan cache:purge --skip-database --skip-files --skip-tokens
```

| Option                | Description                                                              |
|-----------------------|--------------------------------------------------------------------------|
| `--dry-run`           | Counts what would be deleted. Nothing is deleted and no confirmation is asked; the Sanctum step is skipped. |
| `--force`             | Run without confirmation in production.                                  |
| `--limit=`            | Records or files processed per batch.                                    |
| `--store=`            | Cache store (from `config/cache.php`) that must use the `database` driver. |
| `--connection=`       | Database connection used instead of the store's own connection.          |
| `--skip-database`     | Skip the database cache and cache lock tables.                           |
| `--skip-files`        | Skip the file cache (`cache.stores.file.path`, default `storage/framework/cache/data`). |
| `--skip-tokens`       | Skip Sanctum token pruning.                                              |

In production the command asks for confirmation unless `--force` or `--dry-run` is given. A summary is printed and,
for real runs, written to the log. It is safe to schedule:

```php
// routes/console.php
Schedule::command('cache:purge --force')->daily();
```

---

## Honeypot

A simple trap for automated form submissions: a hidden field that real users never fill in.

1. Add the field to your form with the Blade directive:

   ```blade
   <form method="POST" action="/contact">
       @csrf
       @honeypot
       ...
   </form>
   ```

   It renders an off-screen text input (not `display:none`, which simple bots skip) with `aria-hidden`,
   `tabindex="-1"` and `autocomplete="off"`. Outside Blade use `Teksite\Extralaravel\Support\Honeypot::field()`.

2. Protect the route with the `honeypot` middleware alias:

   ```php
   Route::post('/contact', ContactController::class)->middleware('honeypot');
   ```

If the field is present and not empty, the request is aborted with the configured status (403 by default). Set
`extralaravel.honeypot.enabled` to `false` to turn the check off. An invalid configured status falls back to 403.

---

## Blade directives

### `@honeypot`

See [Honeypot](#honeypot).

### `@recursive`

Runs its body once for every **leaf** (non-iterable) value found anywhere inside a nested array or collection.
It compiles to a plain `foreach`, so the body shares the template's variables.

```blade
@recursive($items, $item)
    <li>{{ $item }}</li>
@endrecursive
```

```php
$items = ['a', ['b', ['c', 'd']], 'e'];
// renders a, b, c, d, e
```

- The expression may contain commas: `@recursive(array_merge($a, $b), $leaf)`.
- A non-iterable value (for example `null`) renders nothing.
- Nesting deeper than 64 levels (for example a self-referencing object) throws a `LogicException`.
- The second argument must be a plain variable name; otherwise an `InvalidArgumentException` is thrown at compile time.

---

## Helper functions

Global helpers are defined only if a function with the same name does not exist yet.

| Function                                         | Description                                                                 |
|--------------------------------------------------|-----------------------------------------------------------------------------|
| `changeToSlug(string $slug, string $separator = '-'): Stringable` | Slug that keeps Persian text and splits camelCase. |
| `toEnglishNumber(string $value): string`         | Persian and Arabic digits to English digits.                                |
| `normalizePersianText(?string $string): ?string` | Arabic letter variants to Persian (`ي`→`ی`, `ك`→`ک`, ...) and strips diacritics. |
| `convertSeconds(?int $seconds, ?string $format = 'string')` | Seconds to `HH:MM:SS` or, with `'array'`, to hours/minutes/seconds. Negative values count as 0. |
| `currentUrlWithoutQueries(): string`             | Current URL without the query string.                                       |
| `exploding(?string $string): ?Collection`        | Split by `,` and `،`, trimming each part. Returns `null` for `null`.        |
| `var_export_short(mixed $value, bool $return = false): ?string` | `var_export()` with `[]` syntax; prints unless `$return` is `true`. |
| `arrayToDot(string $name): string`               | `users[0][name]` to `users.0.name`.                                         |
| `dotToArray(string $name): string`               | `users.0.name` to `users[0][name]`.                                         |
| `normalizePath(string $path): string`            | Unify and collapse directory separators. Does **not** resolve `..`, so it is not a traversal guard. |
| `is_rtl(?string $language = null): bool`         | Whether a locale is right-to-left. Region suffixes are ignored (`fa`, `fa_IR`, `fa-IR`). Uses the app locale by default. |
| `get_error(MessageBag $errors, string $name): array` | Validation errors for a field written as `users[0][email]`.             |
| `getStaticData(string $key, mixed $default = null, string $prePath = ''): mixed` | Read values from PHP data files (see below). |

```php
changeToSlug('My Awesome Post');          // "my-awesome-post"
changeToSlug('My Awesome Post', '_');     // "my_awesome_post"
toEnglishNumber('۱۲۳۴۵');                 // "12345"
convertSeconds(3661);                     // "01:01:01"
convertSeconds(3661, 'array');            // ['hours' => 1, 'minutes' => 1, 'seconds' => 1]
exploding('one,two،three')->all();        // ['one', 'two', 'three']
arrayToDot('users[0][name]');             // "users.0.name"
dotToArray('users.0.name');               // "users[0][name]"
is_rtl('fa_IR');                          // true
get_error($errors, 'users[0][email]');    // $errors->get('users.0.email')
```

### `getStaticData()`

Reads static data from PHP files inside the configured data directory (`extralaravel.data_path`, `app/data` by
default) using dot notation. The key starts with the data directory path, followed by the file path and then the keys
inside the returned array.

```text
app/
└── data/
    └── times/
        └── iran.php
```

```php
// app/data/times/iran.php
return [
    'tehran' => ['timezone' => 'Asia/Tehran'],
];
```

```php
getStaticData('app.data.times.iran.tehran.timezone');            // "Asia/Tehran"
getStaticData('app.data.unknown', 'fallback');                   // "fallback"
getStaticData('times.iran.tehran.timezone', 'x', 'app.data');    // "Asia/Tehran" (with $prePath)
```

Resolution tries the longest-to-shortest file match from the left (`app/data/times.php`, then
`app/data/times/iran.php`, ...) and walks the remaining segments through the returned array. Loaded files are cached
per request.

> **Security:** the helper executes the PHP files it resolves. Keys must start with the configured data directory,
> segments containing `..`, slashes or null bytes are rejected, and symlinks that point outside the directory are
> ignored. Never point `data_path` at a directory that contains files you would not want executed, and keep it
> relative to `base_path()`.

---

## Translations

Rule failure messages are English sentences passed through Laravel's translator, so you translate them with a JSON
language file in your application (for example `lang/fa.json`). The package does not ship translation files; add the
keys you need:

```json
{
    "The :attribute must be a valid mobile number.": "...",
    "The :attribute must be a valid mobile number for: :countries": "...",
    "The :attribute must be a valid Iranian national code.": "...",
    "The :attribute must be at least :min characters.": "...",
    "The :attribute must not be greater than :max characters.": "...",
    "The :attribute must contain at least one lowercase letter.": "...",
    "The :attribute must contain at least one uppercase letter.": "...",
    "The :attribute must contain at least one number.": "...",
    "The :attribute must contain at least one symbol.": "...",
    "The :attribute field cannot contain HTML tags": "...",
    "something goes wrong": "..."
}
```

`MobileRule` and `CodeMeliRule` first look for the `validation.mobile` and `validation.invalid_national_code` keys in
your own `validation.php` and use them when they exist. Messages use the attribute display name, so
`validation.attributes` works as usual.

---

## Octane and performance notes

- The config is merged once; Artisan commands and publishing are registered only in the console.
- `getStaticData()` is backed by a **scoped** binding (`StaticData`), so nothing leaks between Octane requests, and
  failed lookups are remembered for the rest of the request.
- Digit conversion, slug normalization and mobile normalization use constant lookup tables and a single pass.
- `MobilePatterns::detectCountry()` skips countries whose dial code cannot match a `+` number before running any
  regular expression.
- The trash route registrar is a singleton; routes use controller-action arrays, not closures.

---

## Upgrade notes

Behavior that differs from older releases:

- **PHP 8.3 or newer** and **Laravel 12 or 13** are required.
- `getStaticData()` only reads inside `extralaravel.data_path`. Set `data_path` to the directory that holds your data
  files and make sure your keys start with it.
- `PasswordRule` defaults are stricter: minimum 8 (was 6), maximum 128, and each failed requirement is a separate
  error. Use `new PasswordRule(6)` or `extralaravel.password.min_length` to restore the old minimum.
- `IpCast` throws `InvalidArgumentException` for values that are not valid IP addresses.
- `@recursive($items, $item)` renders leaves at any depth; the old behavior of rendering a view named `recursive` is
  gone. `RecursiveDirectives::recursive()` is deprecated in favor of `RecursiveDirectives::register($blade)`.
- `TrashOnlyBindingMiddleware` accepts the target route parameter as its argument (for example
  `TrashOnlyBindingMiddleware::class.':post'`; the last route parameter is used when omitted) and also rejects trashed
  parents of nested resources. Routes registered through `trashResource()` configure it automatically.
- The Jalali config key is `jalali_date_locales`. Older releases used the misspelled key `jalalit_date_locales`, which is
  no longer read: rename it in your published `config/extralaravel.php`, otherwise only `['fa']` is used.
- The default `data_path` is `app/data` (relative to `base_path()`); older releases used `/app/data`.
- `PasswordRule` messages are now the English sentences listed under [Translations](#translations). The old
  `the password should ...` translation keys are no longer used, so update your language files.
- `TimeZones::Canada_East_Saskatchewa` and `TimeZones::Antarctica_DumontDUrvil` were misspelled and are now
  `Canada_East_Saskatchewan` and `Antarctica_DumontDUrville`.
- `changeToSlug()` and `SlugCast` split camelCase only at word boundaries (`HTMLParser` becomes `html-parser`).

---

## Testing

```bash
composer install
composer test
```

The suite uses PHPUnit with Orchestra Testbench. GitHub Actions runs it on PHP 8.3 and 8.4 against Laravel 12 and 13.

---

## Security notes

ExtraLaravel does not replace Laravel's own security features. In particular:

- Always use Laravel's authorization system for trash routes, especially `prune` and `flush`.
- Escape user-controlled output. `NoHtmlRule` is validation only.
- Validate and sanitize file uploads independently.
- Keep `getStaticData()` pointed at a directory you control.
- Run `cache:purge --dry-run` first when using it on a new environment.
- Keep the honeypot field name unpredictable if you target determined bots.

Please report security issues privately by email instead of opening a public issue.

---

## Contributing

Contributions are welcome. Before opening a pull request:

1. Write or update tests.
2. Follow PSR-12 and Laravel conventions.
3. Run `composer test`.
4. Keep changes focused and backward compatible where possible, and describe any breaking change.

---

## License

This package is open-sourced software licensed under the MIT license.

---

## Author

Developed by **Sina Zangiband**.

- Websites: [teksite.net](https://teksite.net), [laratek.net](https://laratek.net), [laratek.ir](https://laratek.ir)
- Email: [sina.zangiband@gmail.com](mailto:sina.zangiband@gmail.com)