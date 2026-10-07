<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('ijin_keluar')
    ->select('id_transaksi', 'tanggal_keluar', 'jam_masuk', 'durasi_menit', 'id_peg')
    ->orderBy('tanggal_keluar')
    ->get();

foreach ($rows as $r) {
    echo $r->id_transaksi . ' | ' . $r->tanggal_keluar . ' | jam_masuk=' . ($r->jam_masuk ?? 'null') . ' | durasi=' . ($r->durasi_menit ?? 'null') . PHP_EOL;
}
