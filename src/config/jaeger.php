<?php
return [
	'log' => [
		'max-string-length' => 300,
		'cutoff-indicator' => '...'
	],

    'host' => env('JAEGER_AGENT_HOST', 'localhost').':'.env('JAEGER_AGENT_PORT', 6831),

    'enable-for-console' => env('JAEGER_ENABLE_FOR_CONSOLE', false),

    /**
     * possible values:
     */
    'sampler' => env('JAEGER_SAMPLER', 'adaptive'),

    'sampler-param' => env('JAEGER_SAMPLER_PARAM', '0.001'),
];
