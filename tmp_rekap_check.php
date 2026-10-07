<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$query = DB::table('ijin_keluar as ik')
    ->join('pegawai as p', 'ik.id_peg', '=', 'p.id_peg')
    ->join('subag as sb', 'ik.id_subag', '=', 'sb.id_subag')
    ->join('bagian as b', 'ik.id_bag', '=', 'b.id_bag')
    ->join('perijinan as i', 'ik.id_ijin', '=', 'i.id_ijin')
    ->select('p.nama', 'b.bag', 'sb.sub_bag');

$rows = $query->groupBy('p.id_peg', 'p.nama', 'b.bag', 'sb.sub_bag')->get();
echo $rows->count() . PHP_EOL;
foreach ($rows->take(3) as $row) {
    echo json_encode($row) . PHP_EOL;
}
