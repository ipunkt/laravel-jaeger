<?php
return [

    'host' => env('JAEGER_AGENT_HOST', 'jaeger').':'.env('JAEGER_AGENT_PORT', 6831),

    'disable-global-span' => false,

];
