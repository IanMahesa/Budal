<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PerijinanController;
use App\Http\Controllers\BagianController;
use App\Http\Controllers\SubagController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\GenerateDinController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\IjinKeluarController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\NotifikasiIjinController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth', 'no-cache'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

Route::get('/notifikasi-ijin', [NotifikasiIjinController::class, 'index'])
    ->name('notifikasi-ijin.index');
Route::post('/notifikasi-ijin/read', [NotifikasiIjinController::class, 'read'])
    ->name('notifikasi.read');
Route::post('/notifikasi-ijin/read-all', [NotifikasiIjinController::class, 'readAll'])
    ->name('notifikasi.readAll');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard.index');

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::resource('user', UserController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy',
]);
Route::resource('role', RoleController::class);
Route::post('/pegawai/import', [PegawaiController::class, 'import'])
    ->name('pegawai.import');
Route::resource('pegawai', PegawaiController::class);
Route::get('/pegawai/{id?}/rekap', [PegawaiController::class, 'rekap'])
    ->name('pegawai.rekap');
Route::get('/pegawai/rekap/export/{id?}', [PegawaiController::class, 'exportHarian'])
    ->name('pegawai.rekap.export');

Route::prefix('pegawai')->group(function () {

    Route::get('/rekap/{id?}', [PegawaiController::class, 'rekap'])
        ->name('pegawai.rekap');

});
Route::resource('perijinan', PerijinanController::class)->names([
    'index' => 'ijin.index',
    'create' => 'ijin.create',
    'store' => 'ijin.store',
    'show' => 'ijin.show',
    'edit' => 'ijin.edit',
    'update' => 'ijin.update',
    'destroy' => 'ijin.destroy',
    
]);

Route::resource('bagian', BagianController::class)->names([
    'index' => 'bagian.index',
    'create' => 'bagian.create',
    'store' => 'bagian.store',
    'show' => 'bagian.show',
    'edit' => 'bagian.edit',
    'update' => 'bagian.update',
    'destroy' => 'bagian.destroy',
    
]);

Route::resource('subag', SubagController::class)->names([
    'index' => 'subag.index',
    'create' => 'subag.create',
    'store' => 'subag.store',
    'show' => 'subag.show',
    'edit' => 'subag.edit',
    'update' => 'subag.update',
    'destroy' => 'subag.destroy',
    
]);

Route::get('/pegawai/subbagian/{id}', [PegawaiController::class,'getSubBagian'])
    ->name('pegawai.subbagian');

Route::get('/get-subag/{id_bag?}', [SubagController::class, 'getSubag'])
    ->name('subbag.byBagian');

Route::get('/get-pegawai/{id_subag}', [GenerateController::class, 'getPegawaiBySubag'])
    ->name('qrcode.get-pegawai');

Route::get('/get-perijinan/{id_subag}', [GenerateController::class, 'getPerijinanBySubbag'])
    ->name('qrcode.get-perijinan');

Route::resource('geneqr', GenerateController::class)->names([
    'index' => 'geneqr.index',
    'create' => 'geneqr.create',
    'store' => 'geneqr.store',
    'show' => 'geneqr.show',
    'edit' => 'geneqr.edit',
    'update' => 'geneqr.update',
    'destroy' => 'geneqr.destroy',
]);

// Generate QR Code
Route::post('/qrcode/generate', [GenerateController::class, 'generate'])
    ->name('qrcode.generate');

// Cetak QR Code
Route::post('/qrcode/print', [GenerateController::class, 'print'])
    ->name('qrcode.print');

Route::get('/get-pegawai/{id_subag}', [GenerateDinController::class, 'getPegawaiBySubag'])
    ->name('qrcode.get-pegawai');

Route::get('/get-perijinan/{id_subag}', [GenerateDinController::class, 'getPerijinanBySubbag'])
    ->name('qrcode.get-perijinan');

Route::resource('geneqrdin', GenerateDinController::class)->names([
    'index' => 'geneqrdin.index',
    'create' => 'geneqrdin.create',
    'store' => 'geneqrdin.store',
    'show' => 'geneqrdin.show',
    'edit' => 'geneqrdin.edit',
    'update' => 'geneqrdin.update',
    'destroy' => 'geneqrdin.destroy',
]);

// Generate QR Code
Route::post('/qrcode/generate', [GenerateDinController::class, 'generate'])
    ->name('qrcode.generate');

// Cetak QR Code
Route::post('/qrcode/print', [GenerateDinController::class, 'print'])
    ->name('qrcode.print');

Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
Route::post('/scan/proses', [ScanController::class, 'proses'])->name('scan.proses');
Route::post('/scan/simpan', [ScanController::class,'simpan'])
    ->name('scan.simpan');

Route::get('/keluar/export', [IjinKeluarController::class, 'export'])
    ->name('keluar.export');
Route::resource('keluar', IjinKeluarController::class);

Route::post('/rekap/data', [RekapController::class,'data'])
    ->name('rekap.data');
Route::get('/rekap/pegawai', [RekapController::class,'getPegawai'])
    ->name('rekap.pegawai');
Route::get('/rekap', [RekapController::class,'index'])
    ->name('rekap.index');
Route::get('/rekap/excel', [RekapController::class,'excel'])
    ->name('rekap.excel');
Route::get('/rekap/export', [RekapController::class, 'export'])
    ->name('rekap.export');
Route::get('/qrcode', [QrCodeController::class, 'index'])
    ->name('qrcode.index');
});