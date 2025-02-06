<?php

namespace Plakhin\RequestChronicle\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plakhin\RequestChronicle\Database\Factories\RequestFactory;
use Plakhin\RequestChronicle\Enums\HttpMethod;

/**
 * @property HttpMethod $method
 * @property string $url
 * @property array $headers
 * @property array $payload
 * @property string $raw
 * @property array $ips
 * @property ?Model $model
 * @property Carbon $created_at
 * @property array $flatHeaders
 * @property array $getVariables
 * */
class Request extends Model
{
    /** @use HasFactory<RequestFactory> */
    use HasFactory;

    use MassPrunable;

    public const ?string UPDATED_AT = null;

    protected $fillable = [
        'method',
        'url',
        'headers',
        'payload',
        'raw',
        'ips',
        'model_type',
        'model_id',
    ];

    /** @return array<mixed> */
    protected function casts(): array
    {
        return [
            'method' => HttpMethod::class,
            'headers' => 'array',
            'payload' => 'array',
            'ips' => 'array',
        ];
    }

    public function getTable(): string
    {
        return config('request-chronicle.table_name');
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subHours(
            config('request-chronicle.prune_after_hours', 24 * 7)
        ));
    }

    protected static function newFactory()
    {
        return RequestFactory::new();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return Attribute<array<string>, never> */
    protected function flatHeaders(): Attribute
    {
        return Attribute::make(
            get: fn (): array => array_map(fn ($header) => $header[0], (array) $this->headers),
        );
    }

    /** @return Attribute<array<string>, never> */
    protected function getVariables(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                $vars = [];
                parse_str(urldecode(strval(parse_url($this->url, PHP_URL_QUERY))), $vars);

                return $vars;
            },
        );
    }
}
