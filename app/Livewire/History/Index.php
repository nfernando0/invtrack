<?php

namespace App\Livewire\History;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $selectedLoan;
    public $statusFilter = [];
    public $search = '';
    public $isOverdue = false;
    public $userFilter = '';
    public $dateStart = '';
    public $dateEnd = '';

    public function updatedDateStart() { $this->resetPage(); }
    public function updatedDateEnd() { $this->resetPage(); }
    public function updatedUserFilter() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }
    public function updatedIsOverdue() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }

    public function clearFilter()
    {
        $this->statusFilter = [];
        $this->isOverdue = false;
        $this->search = '';
        $this->userFilter = '';
        $this->dateStart = '';
        $this->dateEnd = '';
        $this->resetPage();
    }

    public function view($id)
    {
        $this->selectedLoan = Loan::with('items.item')->find($id);
    }

    public function render()
    {
        // Hanya yang BUKAN pending
        $query = Loan::with('items.item')->where('status', '!=', 'pending')->latest();
        
        if (auth()->user()->isCustomer()) {
            $query->where('user_id', auth()->id());
        }

        if (!empty($this->statusFilter)) {
            $query->whereIn('status', $this->statusFilter);
        }

        if ($this->isOverdue) {
            $query->where('status', 'approved')
                  ->whereDate('loan_date', '<', now());
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reason', 'like', '%' . $this->search . '%')
                  ->orWhere('note', 'like', '%' . $this->search . '%')
                  ->orWhereHas('items.item', function ($qItem) {
                      $qItem->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if (auth()->user()->isAdmin() && !empty($this->userFilter)) {
            $query->where('user_id', $this->userFilter);
        }

        if (!empty($this->dateStart)) {
            $query->whereDate('loan_date', '>=', $this->dateStart);
        }

        if (!empty($this->dateEnd)) {
            $query->whereDate('loan_date', '<=', $this->dateEnd);
        }

        $users = auth()->user()->isAdmin() ? \App\Models\User::where('role', 'customer')->get() : collect();

        return view('livewire.history.index', [
            'loans' => $query->paginate(5),
            'users' => $users
        ]);
    }
}
