<?php

use App\Http\Controllers\ReceiptController;
use App\Livewire\CategoryTable;
use App\Livewire\Dashboard;
use App\Livewire\InventoryManager;
use App\Livewire\PosTerminal;
use App\Livewire\ProductTable;
use App\Livewire\RoleManagement;
use App\Livewire\SalesReport;
use App\Livewire\StoreSettings;
use App\Livewire\TransactionHistory;
use App\Livewire\UserManagement;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes are grouped by feature and protected by auth middleware.
| Menu permission middleware controls access based on user's menu_permissions.
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| All routes below require authentication via Jetstream/Sanctum.
| Individual routes are further protected by menu.permission middleware.
|
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', Dashboard::class)
        ->middleware('menu.permission:dashboard')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | POS Terminal
    |--------------------------------------------------------------------------
    */
    Route::get('/pos', PosTerminal::class)
        ->middleware('menu.permission:pos')
        ->name('pos');

    /*
    |--------------------------------------------------------------------------
    | Products Management
    |--------------------------------------------------------------------------
    */
    Route::get('/products', ProductTable::class)
        ->middleware('menu.permission:products')
        ->name('products.index');

    /*
    |--------------------------------------------------------------------------
    | Categories Management
    |--------------------------------------------------------------------------
    */
    Route::get('/categories', CategoryTable::class)
        ->middleware('menu.permission:products')
        ->name('categories.index');

    /*
    |--------------------------------------------------------------------------
    | Inventory Management
    |--------------------------------------------------------------------------
    */
    Route::get('/inventory', InventoryManager::class)
        ->middleware('menu.permission:inventory')
        ->name('inventory.index');

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */
    Route::prefix('transactions')->group(function () {
        Route::get('/', TransactionHistory::class)
            ->middleware('menu.permission:transactions')
            ->name('transactions.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', SalesReport::class)
        ->middleware('menu.permission:reports')
        ->name('reports.index');

    /*
    |--------------------------------------------------------------------------
    | User Management (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/users', UserManagement::class)
        ->middleware('menu.permission:users')
        ->name('users.index');

    /*
    |--------------------------------------------------------------------------
    | Role Management (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/roles', RoleManagement::class)
        ->middleware('menu.permission:roles')
        ->name('roles.index');

    /*
    |--------------------------------------------------------------------------
    | Settings (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/settings', StoreSettings::class)
        ->middleware('menu.permission:settings')
        ->name('settings.index');

    /*
    |--------------------------------------------------------------------------
    | Receipt Routes
    |--------------------------------------------------------------------------
    | Receipt viewing is available to any authenticated user who can access
    | the transaction that generated the receipt.
    */
    Route::prefix('receipt')->group(function () {
        Route::get('/{transactionId}', [ReceiptController::class, 'show'])
            ->name('receipt.show');
        Route::get('/number/{transactionNumber}', [ReceiptController::class, 'showByNumber'])
            ->name('receipt.showByNumber');
    });
});
