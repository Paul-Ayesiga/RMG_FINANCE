<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::supportBubble();



Route::get('/accounts/exclude-current', function (Request $request) {
    $userId = Auth::id();
    $query = Account::where('customer_id', '!=', $userId);

    if ($request->search) {
        $query->where('account_number', 'ilike', "%{$request->search}%");
    }

    if ($request->exists('selected')) {
        $query->whereIn('id', $request->input('selected', []));
    }

    // Apply the limit here, before calling get()
    $accounts = $query->limit(10)->get(['id', 'account_number', 'customer_id']);

    return response()->json($accounts->toArray());
})->name('api.other-accounts');


