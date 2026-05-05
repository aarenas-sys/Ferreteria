<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$columnas = \DB::connection()->getSchemaBuilder()->getColumnListing('productos');
echo "Columnas en tabla productos:\n";
foreach ($columnas as $col) {
    echo "  - $col\n";
}
