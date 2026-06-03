<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$router = app('router');
$request = Request::create('http://localhost/admin/media/usage');
$route = $router->getRoutes()->match($request);
$request->setRouteResolver(function () use ($route) {
    return $route;
});

// Bind to container request
app()->instance('request', $request);

echo 'Current route name: '.$request->route()->getName()."\n";

$items = [
    'admin.dashboard',
    'admin.analytics.index',
    'admin.media.index',
];

foreach ($items as $routePattern) {
    $routeParts = explode('.', $routePattern);
    if (count($routeParts) >= 2) {
        $baseRouteName = $routeParts[0].'.'.$routeParts[1];
        $matchPattern = $baseRouteName.'.*';
        $active = $request->routeIs($routePattern) || $request->routeIs($matchPattern);
        echo "Pattern: {$routePattern} | Base: {$baseRouteName} | Match Pattern: {$matchPattern} | Active: ".($active ? 'YES' : 'NO')."\n";
    } else {
        $active = $request->routeIs($routePattern);
        echo "Pattern: {$routePattern} | Active: ".($active ? 'YES' : 'NO')."\n";
    }
}
