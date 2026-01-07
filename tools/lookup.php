<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the framework
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$code = $argv[1] ?? '5901466100011';

try {
    $service = $app->make(Platform\InStoreProductScanner\Services\ProductLookupService::class);
    $result = $service->findByCode($code);
    echo json_encode(['code' => $code, 'result' => $result], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
