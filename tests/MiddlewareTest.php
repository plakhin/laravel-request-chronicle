<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Uri;
use Plakhin\RequestChronicle\Enums\HttpMethod;
use Plakhin\RequestChronicle\Http\Middleware\SaveRequest;
use Plakhin\RequestChronicle\Tests\TestModel;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\withHeaders;

beforeEach(function () {
    $this->table = config()->string('request-chronicle.table_name');
    $this->testRouteName = 'test';

    assertDatabaseEmpty($this->table);
    expect(Route::has($this->testRouteName))->toBeFalse();
});

test('middleware can be attached', function () {
    makeRouteWithMiddleware($this->testRouteName);
    expect(Route::getRoutes()->getByName($this->testRouteName)->gatherMiddleware())->toContain(SaveRequest::class);
});

it('saves GET requests', function () {
    $uri = Uri::of(makeRouteWithMiddleware($this->testRouteName).'?foo=bar');

    withHeaders(['X-First' => 'foo'])->get($uri)->assertNoContent();

    assertDatabaseCount($this->table, 1);
    assertDatabaseHas($this->table, [
        'method' => HttpMethod::GET,
        'url' => $uri,
        'headers' => json_encode([
            'host' => [$uri->host()],
            'user-agent' => ['Symfony'],
            'accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'],
            'accept-language' => ['en-us,en;q=0.5'],
            'accept-charset' => ['ISO-8859-1,utf-8;q=0.7,*;q=0.7'],
            'x-first' => ['foo'],
        ]),
        'payload' => json_encode([]),
        'raw' => "GET /{$uri->path()}?{$uri->query()} HTTP/1.1\r\nAccept:          text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\nAccept-Charset:  ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\nAccept-Language: en-us,en;q=0.5\r\nHost:            {$uri->host()}\r\nUser-Agent:      Symfony\r\nX-First:         foo\r\n\r\n",
        'ips' => json_encode(['127.0.0.1']),
    ]);
});

it('saves POST requests', function () {
    $uri = Uri::of(makeRouteWithMiddleware($this->testRouteName).'?foo=bar');

    withHeaders(['X-First' => 'foo'])->post($uri, ['baz' => 'qux'])->assertNoContent();

    assertDatabaseCount($this->table, 1);
    assertDatabaseHas($this->table, [
        'method' => HttpMethod::POST,
        'url' => $uri,
        'headers' => json_encode([
            'host' => [$uri->host()],
            'user-agent' => ['Symfony'],
            'accept' => ['text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'],
            'accept-language' => ['en-us,en;q=0.5'],
            'accept-charset' => ['ISO-8859-1,utf-8;q=0.7,*;q=0.7'],
            'x-first' => ['foo'],
            'content-type' => ['application/x-www-form-urlencoded'],
        ]),
        'payload' => json_encode(['baz' => 'qux']),
        'raw' => "POST /{$uri->path()}?{$uri->query()} HTTP/1.1\r\nAccept:          text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\nAccept-Charset:  ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\nAccept-Language: en-us,en;q=0.5\r\nContent-Type:    application/x-www-form-urlencoded\r\nHost:            {$uri->host()}\r\nUser-Agent:      Symfony\r\nX-First:         foo\r\n\r\n",
        'ips' => json_encode(['127.0.0.1']),
    ]);
});

it('attaches request to the model by route parameter', function () {
    TestModel::migrate();
    $testModel = TestModel::create(['slug' => fake()->word()]);

    expect($testModel->requests()->doesntExist())->toBeTrue();
    expect(Route::has('test'))->toBeFalse();

    Route::get('{model:slug}/test', fn (TestModel $model) => response()->noContent())
        ->middleware(SaveRequest::class.':model')
        ->name('test');

    get(route('test', $testModel))->assertNoContent();

    expect($testModel->requests()->exists())->toBeTrue();
});

function makeRouteWithMiddleware(string $name): string
{
    Route::any($name, fn () => response()->noContent())
        ->middleware(SaveRequest::class)
        ->name($name);

    return route($name);
}
