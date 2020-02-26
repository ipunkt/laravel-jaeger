<?php
return [
    'disabled' => env('JAEGER_DISABLE', false),


	'log' => [
	    'database' => env('JAEGER_LOG_DATABASE', false),
		'max-string-length' => 300,
		'cutoff-indicator' => '...'
	],

    'host' => env('JAEGER_AGENT_HOST', 'localhost').':'.env('JAEGER_AGENT_PORT', 6831),

    'enable-for-console' => env('JAEGER_ENABLE_FOR_CONSOLE', false),

    /**
     * possible values:
     * - probabilistic
     *   - param is the chance in percent that a request will be logged
     * - rate-limiting
     * - adaptive
     *   -
     * - const
     *   - ignores param, all requests will be logged
     */
    'sampler' => env('JAEGER_SAMPLER', 'const'),

    'sampler-param' => env('JAEGER_SAMPLER_PARAM', '0.001'),
];
