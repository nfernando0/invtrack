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

        // Data for alerts
        $pendingLoansCount = 0;
        $lowStockItemsCount = 0;
        $overdueLoansCount = 0;
        $upcomingReturnsCount = 0;

        if ($user->isAdmin()) {
            $pendingLoansCount = \App\Models\Loan::where('status', 'pending')->count();
            $lowStockItemsCount = \App\Models\Item::where('stock', '<=', 5)->count();
        } else {
            $overdueLoansCount = \App\Models\Loan::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('loan_date', '<', now()->toDateString())
                ->count();
                
            $upcomingReturnsCount = \App\Models\Loan::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereBetween('loan_date', [now()->toDateString(), now()->addDays(3)->toDateString()])
                ->count();
        }

        $popularItems = \App\Models\Item::whereHas('loanItems', function($query) use ($user) {
            $query->whereHas('loan', function($q) use ($user) {
                $q->whereIn('status', ['approved', 'returned']);
                if ($user->isCustomer()) {
                    $q->where('user_id', $user->id);
                }
            });
        })
        ->withSum(['loanItems as total_borrowed' => function($query) use ($user) {
            $query->whereHas('loan', function($q) use ($user) {
                $q->whereIn('status', ['approved', 'returned']);
                if ($user->isCustomer()) {
                    $q->where('user_id', $user->id);
                }
            });
        }], 'quantity')
        ->orderByDesc('total_borrowed')
        ->take(5)
        ->get();

        return view('dashboard', compact(
            'totalLoanedItems', 
            'totalReturnedItems', 
            'totalAvailableItems', 
            'recentLoans',
            'pendingLoansCount',
            'lowStockItemsCount',
            'overdueLoansCount',
            'upcomingReturnsCount',
            'popularItems'
        ));
    })->name('dashboard');

    Route::get('/item', App\Livewire\Item\Index::class)->name('item.index');
    Route::get('/item/create', App\Livewire\Item\Create::class)->name('item.create');
    Route::get('/item/edit/{item}', App\Livewire\Item\Edit::class)->name('item.edit');

    Route::get('/category', App\Livewire\Category\Index::class)->name('category.index');
    Route::get('/category/create', App\Livewire\Category\Create::class)->name('category.create');
    Route::get('/category/edit/{category}', App\Livewire\Category\Edit::class)->name('category.edit');

    Route::get('/loan', App\Livewire\Loan\Index::class)->name('loan.index');
    Route::get('/loan/create', App\Livewire\Loan\Create::class)->name('loan.create');
    Route::get('/loan/edit/{loan}', App\Livewire\Loan\Edit::class)->name('loan.edit');

    Route::get('/history', App\Livewire\History\Index::class)->name('history.index');
});

require __DIR__ . '/settings.php';
