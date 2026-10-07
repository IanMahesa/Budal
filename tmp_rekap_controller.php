<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\RekapController;

$request = Request::create('/rekap/data', 'POST', [
    'periode' => 'harian',
    'tanggal' => '2026-07-16',
    '_token' => 'dummy'
]);

$controller = new RekapController();
$response = $controller->data($request);
echo $response->getContent();
