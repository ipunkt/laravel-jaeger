<?php namespace Ipunkt\LaravelJaeger\Middleware;

use Illuminate\Http\Request;
use Ipunkt\LaravelJaeger\Context\MasterSpanContext;
use Illuminate\Support\Str;
use Jaeger\Codec\CodecInterface;

/**
 * Class Jaeger
 */
class Jaeger
{
	/**
	 * @var CodecInterface
	 */
	private $decoder;

	/**
	 * Jaeger constructor.
	 * @param CodecInterface $decoder
	 */
	public function __construct( CodecInterface $decoder) {
		$this->decoder = $decoder;
	}

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
    	$traceData = $request->header();

        app('context')->parse($request->url(), $traceData);
    }

    private function registerContext(): void
    {
        $instance = app(MasterSpanContext::class);
        app()->instance('context', $instance);
        app()->instance('current-context', $instance);
    }
}