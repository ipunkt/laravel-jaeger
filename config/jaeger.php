<?php
return [
    'disabled' => env('JAEGER_DISABLE', false),

	/**
	 * Available codcs:
	 * - text - reads and writes the common uber-trace-id style header
	 *   - Example :
	 * - json - a custom codec which reads and writes information to json
	 *   - Example:
	 */
	'codecs' => [
		// Codecs used to accept
		/**
		 * Codecs used extract context from http,rabbitmq etc header
		 * codecs will be tried top to bottom and the first one to find a context will be used if multiple are present
		 * - ipunkt/laravel-jaeger (this package) reads from http headers on incoming requests
		 * - ipunkt/laravel-jaeger-rabbitmq reads from rabbitmq message header
		 */
		'extract' => [
			'uber-trace-id' => 'text',
			'x-trace' => 'json',
		],

		/**
		 * Codecs used inject context into http,rabbitmq etc header
		 * All codecs given here will be injected
		 *
		 * This has no effect with this package alone but rather with packages bridging to other communication platforms
		 * e.g.
		 * - ipunkt/laravel-jaeger-guzzle
		 * - ipunkt/laravel-jaeger-rabbitmq
		 * - anything that uses app('context')->inject() or app('current-context')->inject()
		 */
		'inject' => [
			'uber-trace-id' => 'text',
			'x-trace' => 'json',
		],
	],


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
