<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\Beneficiary;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::supportBubble();



Route::get('/accounts', function (Request $request) {
    $userId = Auth::id();
    $customer = Auth::user()->customer;
    $query = Account::where('customer_id', $customer->id);

    if ($request->search) {
        $query->where('account_number', 'ilike', "%{$request->search}%");
    }

    if ($request->exists('selected')) {
        $query->whereIn('id', $request->input('selected', []));
    }

    // Apply the limit here, before calling get()
    $accounts = $query->get(['id', 'account_number', 'customer_id']);

    return response()->json($accounts->toArray());

})->name('api.accounts')->middleware('web');
Route::get('/beneficiaries', function (Request $request) {
    $userId = Auth::id();

    $query = Beneficiary::where('user_id', $userId);

    if ($request->search) {
        $query->where('account_number', 'ilike', "%{$request->search}%");
    }

    if ($request->exists('selected')) {
        $query->whereIn('id', $request->input('selected', []));
    }

    // Apply the limit here, before calling get()
    $beneficiaries = $query->get(['id', 'account_number', 'nickname']);

    return response()->json($beneficiaries->toArray());

})->name('api.beneficiaries')->middleware('web');


