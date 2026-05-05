<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::table('sessions')->getConnection()->getSchemaBuilder()->getColumnListing('sessions');
echo "Columnas de la tabla sessions:\n";
foreach ($columns as $col) {
    echo "  - {$col}\n";
}
