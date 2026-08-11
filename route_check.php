<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$routes = [];
foreach (Illuminate\Support\Facades\Route::getRoutes() as $route) {
    $name = $route->getName();
    if ($name) {
        $routes[$name] = true;
    }
}
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/resources/views'));
$missing = [];
foreach ($files as $file) {
    if (! $file->isFile()) continue;
    if (! preg_match('/\.blade\.php$/', $file->getFilename())) continue;
    $text = file_get_contents($file->getPathname());
    if (preg_match_all('/route\(\s*(["\"])((?:[^\\\\]|\\\\.)*?)\1/', $text, $m)) {
        foreach ($m[2] as $name) {
            if (! isset($routes[$name])) {
                $missing[$name][] = $file->getPathname();
            }
        }
    }
}
foreach ($missing as $name => $files) {
    echo "MISSING_ROUTE:$name\n";
    foreach ($files as $file) {
        echo "  $file\n";
    }
}
echo "TOTAL_MISSING=" . count($missing) . "\n";
echo "TOTAL_ROUTES=" . count($routes) . "\n";
