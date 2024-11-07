<?php

use App\Livewire\Accounts\AccountsOverview;
use App\Livewire\Accounts\AccountTypes;
use App\Livewire\Clients\Edit as ClientEdit;
use App\Livewire\Clients\Index as Clients;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\CustomerDashboard;
use App\Livewire\CustomerFolder\MyAccounts\Overview as MyAccounts;
use App\Livewire\CustomerFolder\MyLoans\Overview as MyLoans;
use App\Livewire\CustomerFolder\RMGPAY as RMGPAY;
use App\Livewire\Loans\Overview as Loans;
use App\Livewire\Loans\LoanProducts;
use App\Livewire\Transactions\Overview as TransactionsOverview;
use App\Livewire\Staff\Overview as Staff;
use App\Livewire\BankCharges\Overview as BankCharge;
use App\Livewire\Taxes\Overview as BTax;
use App\Livewire\Admin\RolePermissionManager;
use App\Livewire\Admin\SendNotification;


use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('landing');
});

Route::middleware(['auth','verified','role:super-admin'])->group(function(){
    Route::get('/dashboard',Dashboard::class)->name('dashboard');
    Route::get('/clients',Clients::class)->name('clients');
    Route::get('/clients/{customer}/edit',ClientEdit::class)->name('edit-client');
    Route::get('/account_types', AccountTypes::class)->name('account-types');
    Route::get('accounts-overview', AccountsOverview::class)->name('accounts-overview');
    Route::get('/loan-products', LoanProducts::class)->name('loan-products');
    Route::get('/loans',Loans::class)->name('loans');
    Route::get('/transactions-overview',TransactionsOverview::class)->name('transactions-overview');
    Route::get('/staff',Staff::class)->name('staff');
    Route::get('/settings/bank-charges', BankCharge::class)->name('bank-charges');
    Route::get('/settings/taxes', BTax::class)->name('taxes');
    Route::get('/admin/roles', RolePermissionManager::class)->name('admin.roles');

});

Route::middleware(['auth','verified','role:super-admin|staff'])->group(function(){
    Route::get('/dashboard',Dashboard::class)->name('dashboard');
    Route::get('/clients',Clients::class)->name('clients');
    Route::get('/clients/{customer}/edit',ClientEdit::class)->name('edit-client');
    Route::get('/account_types', AccountTypes::class)->name('account-types');
    Route::get('accounts-overview', AccountsOverview::class)->name('accounts-overview');
    Route::get('/loan-products', LoanProducts::class)->name('loan-products');
    Route::get('/loans',Loans::class)->name('loans');
    Route::get('/transactions-overview',TransactionsOverview::class)->name('transactions-overview');
});

Route::middleware(['auth','verified','role:customer'])->group(function(){
    Route::get('/customer-dashboard',CustomerDashboard::class)->name('customer-dashboard');
    Route::get('/customer/my-accounts',MyAccounts::class)->name('my-accounts');
    Route::get('/customer/my-loans',MyLoans::class)->name('my-loans');
    Route::get('/rmgpay',RMGPAY::class)->name('rmgpay');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


require __DIR__.'/auth.php';
Route::supportBubble();

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/admin/notifications/send', SendNotification::class)->name('admin.notifications.send');
});



