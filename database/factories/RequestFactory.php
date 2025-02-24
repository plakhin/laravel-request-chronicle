<?php

declare(strict_types=1);

namespace Plakhin\RequestChronicle\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Stringable;
use Plakhin\RequestChronicle\Enums\HttpMethod;
use Plakhin\RequestChronicle\Models\Request;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Request>
 */
class RequestFactory extends Factory
{
    protected $model = Request::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $host = fake()->domainName();
        $method = fake()->randomElement([HttpMethod::GET, HttpMethod::POST]); /** @var HttpMethod $method */
        $url = ($qty = fake()->numberBetween(0, 3))
            ? '?'.Arr::query(array_combine((array) fake()->words($qty), (array) fake()->words($qty)))
            : '';

        $userAgent = fake()->userAgent();

        $headers = [
            'accept' => ['*/*'],
            'user-agent' => [$userAgent],
            'host' => [$host],
        ];

        $payload = $method === HttpMethod::POST
            ? array_combine((array) fake()->words($qty = fake()->numberBetween(0, 3)), (array) fake()->words($qty))
            : [];

        $raw = str("{$method->name} /{$url} HTTP/2.0\n")
            ->append("Accept:         */*\n")
            ->append("Host:           {$host}\n")
            ->append("User-Agent:     {$userAgent}\n")
            ->when(
                count($payload),
                fn (Stringable $raw) => $raw
                    ->append('Content-Length: '.mb_strlen((string) json_encode($payload))."\n")
                    ->append("Content-Type:   application/json\n\n")
                    ->append((string) json_encode($payload))
            )->toString();

        $ip = fake()->ipv4();

        return [
            'method' => $method,
            'url' => config('app.scheme').$host.$url,
            'headers' => $headers,
            'payload' => $payload,
            'raw' => $raw,
            'ips' => [$ip],
        ];
    }

    public function forModel(Model $model): Factory
    {
        return $this->state(fn (array $attributes) => [
            'model_id' => $model->getKey(),
            'model_type' => $model::class,
        ]);
    }
}
