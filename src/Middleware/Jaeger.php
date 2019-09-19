<?php namespace Ipunkt\LaravelJaeger\Middleware;

use Ipunkt\LaravelJaeger\Context\MasterSpanContext;
use Illuminate\Support\Str;

/**
 * Class Jaeger
 */
class Jaeger
{

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     */
    public function handle($request, \Closure $next)
    {
        if( config('jaeger.disabled') )
            return $next($request);

        $this->registerContext();

        app('context')->start();
        $this->parseRequest($request);

        $response = $next($request);

        app('context')->setPrivateTags([
            'user_id' => optional(auth()->user())->id ?? "-",
            'company_id' => optional(auth()->user())->company_id ?? "-",

            'request_host' => $request->getHost(),
            'request_path' => $path = $request->path(),
            'request_method' => $request->method(),

            'api' => Str::contains($path, 'api'),
            'response_status' => $response->getStatusCode(),
            'error' => !$response->isSuccessful(),
        ]);

        return $response;
    }

    /**
     * @param $request
     * @param $response
     */
    public function terminate($request, $response)
    {
        app('context')->finish();
    }


    /**
     * @param Request $request
     */
    private function parseRequest($request)
    {

        $header = $request->header('context', '{}');

        $traceData = json_decode($header, true);
        if(!is_array($traceData))
            $traceData = [];

        app('context')->parse($request->url(), $traceData);
    }

    private function registerContext(): void
    {
        $instance = app(MasterSpanContext::class);
        app()->instance('context', $instance);
        app()->instance('current-context', $instance);
    }
}