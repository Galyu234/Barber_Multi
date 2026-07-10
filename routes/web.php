<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterBarbershopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BarbershopController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\QueueController as AdminQueueController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ScannerController;
use App\Http\Controllers\Admin\BranchProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/branch/{code}', [HomeController::class, 'branchDetail'])->name('branch.detail');
Route::get('/monitor/{code}', [HomeController::class, 'queueMonitor'])->name('queue.monitor');

// Queue Actions (scan QR cabang → branch detail → join/leave/status)
Route::get('/queue/join/{branch_code}',  [QueueController::class, 'showJoin'])->name('queue.join');
Route::post('/queue/join/{branch_code}', [QueueController::class, 'join'])->name('queue.join.post');
Route::get('/queue/leave/{branch_code}', [QueueController::class, 'showLeave'])->name('queue.leave');
Route::post('/queue/leave/{branch_code}',[QueueController::class, 'leave'])->name('queue.leave.post');
Route::get('/queue/status/{branch_code}/{token}', [QueueController::class, 'status'])->name('queue.status');

/*
|--------------------------------------------------------------------------
| AJAX / API Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/branches', [HomeController::class, 'apiBranches'])->name('branches');
    Route::get('/branch/{code}/queue', [HomeController::class, 'apiBranchQueue'])->name('branch.queue');
    Route::get('/queue/status/{branch_code}/{token}', [QueueController::class, 'apiStatus'])->name('queue.status');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// Public Registration — Barbershop Owner
Route::get('/register-barbershop',  [RegisterBarbershopController::class, 'showForm'])
    ->name('register.barbershop')->middleware('guest');
Route::post('/register-barbershop', [RegisterBarbershopController::class, 'register'])
    ->name('register.barbershop.post')->middleware('guest');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super_admin,admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Barbershops — only super_admin
    Route::resource('barbershops', BarbershopController::class)
        ->middleware('role:super_admin');
    Route::post('/barbershops/{barbershop}/toggle-suspend', [BarbershopController::class, 'toggleSuspend'])
        ->name('barbershops.toggle-suspend')
        ->middleware('role:super_admin');

    // Branch Profile (Self Service untuk Admin Cabang)
    Route::get('/profile/branch', [BranchProfileController::class, 'edit'])->name('profile.branch');
    Route::put('/profile/branch', [BranchProfileController::class, 'update'])->name('profile.branch.update');

    // User Management — only super_admin
    Route::resource('users', UserController::class)
        ->middleware('role:super_admin');

    // Branches
    // - super_admin: akses penuh termasuk delete
    // - tenant admin (role:admin dengan barbershop_id): bisa index, create, store, edit, update, destroy cabangnya sendiri
    // - admin cabang lama (role:admin dengan branch_id): hanya index, edit, update
    Route::resource('branches', BranchController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/branches/{branch}/qrcode',               [BranchController::class, 'qrCode'])->name('branches.qrcode');
    Route::get('/branches/{branch}/qrcode/download/png',  [BranchController::class, 'downloadQr'])->defaults('type', 'branch')->name('branches.qrcode.download');
    Route::get('/branches/{branch}/qrcode/download/svg',  [BranchController::class, 'downloadQrSvg'])->name('branches.qrcode.download.svg');
    // Keep backward compat for old PNG download URL
    Route::get('/branches/{branch}/qrcode/{type}/download', [BranchController::class, 'downloadQr'])->name('branches.qrcode.download.typed');

    // Queues monitor
    Route::get('/queues',            [AdminQueueController::class, 'index'])->name('queues.index');
    Route::delete('/queues/{queue}', [AdminQueueController::class, 'destroy'])->name('queues.destroy');

    // Stats
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

    // QR Scanner (tersedia untuk semua admin)
    Route::get('/scanner',          [ScannerController::class, 'index'])->name('scanner.index');
    Route::post('/scanner/lookup',  [ScannerController::class, 'lookup'])->name('scanner.lookup');
    Route::post('/scanner/complete',[ScannerController::class, 'complete'])->name('scanner.complete');

    // AJAX admin monitor
    Route::get('/api/monitor', [AdminQueueController::class, 'apiMonitor'])->name('api.monitor');
});
