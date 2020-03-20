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
            'environment' => app()->environment(),

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

    	$uberIdHeader = $request->header('UBER-TRACE-ID');
    	if( !empty($uberIdHeader) ) {
		    app('context')->fromUberId($request->url(), $uberIdHeader);
		    return;
	    }

        $xHeader = $request->header('X-TRACE', '{}');
        $jsonHeader = urldecode($xHeader);

        $traceData = json_decode($jsonHeader, true);
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