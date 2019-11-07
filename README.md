# laravel-jaeger
This package uses `code-tool/jaeger-client-php` to log requests to jaeger.

## Install

	composer install ipunkt/laravel-jaeger:^1.0
	
Add the Middleware `Ipunkt\LaravelJaeger\Middleware\Jaeger` to the routes you want to track, or as generall midleware if
you wish to track all requests

## Configuration
The default configuration should work for most micro services or small scale apps. It assumes a jaeger agent is running
on localhost and service on port 6831

The default sampler is the constant sampler, set to `true`. This means all requests will be logged.

### Rancherize
If you use rancherize you can get the jaeger agent running as a sidekick in your containers network by adding the
`ipunkt/rancherize-jaeger` plugin and setting `"jaeger": { "host":"jaeger.domain.ex:port" }`

Note however that the jaeger agent running in a sidekick will react badly to the main service restarting or crashing.
Consider using a keepalive service which does nothing running as main service and both the jaeger agent and your actual
app joining this keepalive services' network

## Child spans
One thign

# Thanks
Thanks to  
https://mauri870.github.io/blog/posts/opentracing-jaeger-laravel/ for the idea and implementation this package is and
will be built uppon