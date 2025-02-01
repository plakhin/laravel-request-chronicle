<?php

namespace Plakhin\RequestChronicle\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as HttpRequest;
use Plakhin\RequestChronicle\Enums\HttpMethod;
use Plakhin\RequestChronicle\Models\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveRequest
{
    private ?string $attr = null;

    public function __construct(private Registrar $router)
    {
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(HttpRequest): (Response)  $next
     */
    public function handle(HttpRequest $request, Closure $next, ?string $attr = null): Response
    {
        $this->router->substituteImplicitBindings($request->route());

        $this->attr = $attr;

        return $next($request);
    }

    public function terminate(HttpRequest $request): void
    {
        Request::create([
            'method' => HttpMethod::{$request->method()},
            'url' => $request->fullUrl(),
            'headers' => array_diff_key($request->header(), ['cookie' => []]),
            'payload' => $request->post(),
            'raw' => (string) $request,
            'ips' => $request->ips(),
            'model_type' => is_a($request->{$this->attr}, Model::class) ? $request->{$this->attr}::class : null,
            'model_id' => is_a($request->{$this->attr}, Model::class) ? $request->{$this->attr}->getKey() : null,
        ]);
    }
}
