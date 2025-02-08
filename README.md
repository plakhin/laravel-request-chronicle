# Save incoming HTTP requests into the DB

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plakhin/laravel-request-chronicle.svg?style=flat-square)](https://packagist.org/packages/plakhin/laravel-request-chronicle)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plakhin/laravel-request-chronicle/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plakhin/laravel-request-chronicle/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/plakhin/laravel-request-chronicle/check-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plakhin/laravel-request-chronicle/actions?query=workflow%3A"Check+PHP+code+style+issues"+branch%3Amain)
[![GitHub PHPStan Action Status](https://img.shields.io/github/actions/workflow/status/plakhin/laravel-request-chronicle/phpstan.yml?branch=main&label=static%20analysis&style=flat-square)](https://github.com/plakhin/laravel-request-chronicle/actions?query=workflow%3A"PHPStan"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/plakhin/laravel-request-chronicle.svg?style=flat-square)](https://packagist.org/packages/plakhin/laravel-request-chronicle)

## Installation

You can install the package via composer:

```bash
composer require plakhin/laravel-request-chronicle
```

Then you may optionally publish the config file with:

```bash
php artisan vendor:publish --tag="request-chronicle-config"
```

This is the contents of the published config file:

```php
return [
    'table_name' => 'request_chronicle',
    'prune_after_hours' => 24 * 7,
];
```


Then you need to publish and run the migrations with:

```bash
php artisan vendor:publish --tag="request-chronicle-migrations"
php artisan migrate
```

## Usage

If you want to save every HTTP request to the database, you may append it to the global middleware stack in your application's `bootstrap/app.php` file:

```php
use Plakhin\RequestChronicle\Http\Middleware\SaveRequest;

->withMiddleware(function (Middleware $middleware) {
     $middleware->append(SaveRequest::class);
})
```

You can also apply the middleware to a specific route(s) only. Additionally you can specify the model you wish attach (using `MorphTo` relationship) requests to, [Route Model Binding](https://laravel.com/docs/routing#route-model-binding) should be used in this case:

```php
use App\Models\YourModel;
use Plakhin\RequestChronicle\Http\Middleware\SaveRequest;

Route::get('{model:slug}/test', function (YourModel $model) {
    //
})->middleware(SaveRequest::class.':model');
```

All the requests will be stored in the database table specified in the config.

You can retrieve the requests using the `Request` model:

```php
use Plakhin\RequestChronicle\Models\Request;

$requests = Request::all();
```

You can also add the `MorphMany` relationship to your model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class YourModel extends Model
{
    public function requests(): MorphMany
    {
        return $this->morphMany(Request::class, 'model');
    }
}
```

### Pruning the database table
The `Request` model uses the [Laravel's `MassPrunable` trait](https://laravel.com/docs/eloquent#mass-pruning). In the config file, you can specify the number of hours to keep records using `prune_after_hours` key and then to schedule the `model:prune` command, as instructed in [Laravel's docs](https://laravel.com/docs/scheduling#scheduling-artisan-commands). You'll have to explicitly add the model class:

```php
// in bootstrap/app.php

->withSchedule(function (Schedule $schedule) {
    $schedule->command('model:prune', [
        '--model' => [
            \Plakhin\RequestChronicle\Models\Request::class,
        ],
    ])->daily();
})
```


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Stanislav Plakhin](https://github.com/plakhin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
