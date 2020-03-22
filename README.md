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
A child span can be created using 

	$span = app('current-context')->child('operation name');
	
This will also set `app('current-context')` to the new span so creating further child spans will always descent from the
last created child span.

The returned `$span` here is actually a wrapper. Upon destruction it checks if the span is currently the active
`current-context` and if so then it will set the `current-context` back to the parent context

### Child span examples

The following code will create the span hirarchy `App Span` -> `Algorithm` -> `First Pass`

```php
class Algorithm {
 public function run() {
	$span = app('current-context')->child('Algorithm');
	
	$this->firstPass();
 };
 
 protected function firstPass() {
	$span = app('current-context')->child('First Pass');
	
	// do something
 } 
}
```

#### A note on loops
Loops in php require special attention when creating child spans because they do NOT unset variables on leaving. The
following code will not act as expected:

```php
for($j = 0; $j < 2 ; ++$j ) {
	$span = app('current-context')->child('Pass '.$j);
}
$span = app('current-context')->child('End');
```

Instead it will create the following hierarchy:

- Application Span
  - Pass 0
  - Pass 1
    - End

For loops to work as expected you should explicitly unset the $span variable at the end of the loop
```php
for($j = 0; $j < 2 ; ++$j ) {
	$span = app('current-context')->child('Pass '.$j);
	
	unset($span);
}
$span = app('current-context')->child('End');
```

will produce the expected

- Application Span
  - Pass 0
  - Pass 1
  - End

# Thanks
Thanks to  
https://mauri870.github.io/blog/posts/opentracing-jaeger-laravel/ for the idea and implementation this package is and
will be built uppon