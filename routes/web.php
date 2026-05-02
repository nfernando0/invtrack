<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        $loanQuery = \App\Models\LoanItem::whereHas('loan', function ($query) use ($user) {
            $query->where('status', 'approved');
            if ($user->isCustomer()) {
                $query->where('user_id', $user->id);
            }
        });
        $totalLoanedItems = $loanQuery->sum('quantity');

        $returnedQuery = \App\Models\LoanItem::whereHas('loan', function ($query) use ($user) {
            $query->where('status', 'returned');
            if ($user->isCustomer()) {
                $query->where('user_id', $user->id);
            }
        });
        $totalReturnedItems = $returnedQuery->sum('quantity');

        $totalAvailableItems = \App\Models\Item::sum('stock');

        $recentLoansQuery = \App\Models\Loan::with(['items.item', 'user'])->latest();
        if ($user->isCustomer()) {
            $recentLoansQuery->where('user_id', $user->id);
        }
        $recentLoans = $recentLoansQuery->take(5)->get();

        return view('dashboard', compact('totalLoanedItems', 'totalReturnedItems', 'totalAvailableItems', 'recentLoans'));
    })->name('dashboard');

    Route::get('/item', App\Livewire\Item\Index::class)->name('item.index');
    Route::get('/item/create', App\Livewire\Item\Create::class)->name('item.create');
    Route::get('/item/edit/{item}', App\Livewire\Item\Edit::class)->name('item.edit');

    Route::get('/loan', App\Livewire\Loan\Index::class)->name('loan.index');
    Route::get('/loan/create', App\Livewire\Loan\Create::class)->name('loan.create');
    Route::get('/loan/edit/{loan}', App\Livewire\Loan\Edit::class)->name('loan.edit');
});

require __DIR__ . '/settings.php';
