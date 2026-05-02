<?php

namespace App\Livewire\Loan;

use App\Models\Loan;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;

    public $loanId;
    public $selectedLoan;
    public $selectedLoans = [];
    public $selectAll = false;
    public $search = '';
    public $userFilter = '';
    public $dateStart = '';
    public $dateEnd = '';

    public function updatedDateStart()
    {
        $this->resetPage();
    }

    public function updatedDateEnd()
    {
        $this->resetPage();
    }

    public function updatedUserFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedIsOverdue()
    {
        $this->resetPage();
    }

    public function clearFilter()
    {
        $this->search = '';
        $this->userFilter = '';
        $this->dateStart = '';
        $this->dateEnd = '';
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedLoans = \App\Models\Loan::where('status', 'pending')->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedLoans = [];
        }
    }

    public function updatedSelectedLoans()
    {
        // Uncheck 'Select All' if user manually deselects an item
        if (count($this->selectedLoans) === 0) {
            $this->selectAll = false;
        }
    }

    public function confirmDelete($id)
    {
        $this->loanId = $id;
    }

    public function delete()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        if ($this->loanId) {
            $loan = Loan::with('items.item')->find($this->loanId);

            if ($loan) {
                // Restore stock for each item only if the loan is not already returned or rejected
                if (in_array($loan->status, ['pending', 'approved'])) {
                    foreach ($loan->items as $loanItem) {
                        if ($loanItem->item) {
                            $loanItem->item->increment('stock', $loanItem->quantity);
                        }
                    }
                }

                // Delete related items first
                $loan->items()->delete();
                $loan->delete();

                // Reset ID
                $this->loanId = null;

                // Close modal
                $this->dispatch('modal-close', name: 'delete-loan');

                // Show toast
                Flux::toast(
                    text: 'Data peminjaman berhasil dihapus dan stok barang dikembalikan!',
                    variant: 'success'
                );
            }
        }
    }

    public function view($id)
    {
        $this->selectedLoan = Loan::with('items.item')->find($id);
    }

    public function confirmApprove($id)
    {
        $this->loanId = $id;
    }

    public function approve()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        if ($this->loanId) {
            $loan = Loan::with('items.item')->find($this->loanId);

            if ($loan) {
                // Change status to 'approved'
                $loan->update(['status' => 'approved']);

                // Reset ID
                $this->loanId = null;

                // Close modal
                $this->dispatch('modal-close', name: 'approve-loan');

                // Show toast
                Flux::toast(
                    text: 'Data peminjaman berhasil disetujui!',
                    variant: 'success'
                );
            }
        }
    }
    public function bulkApprove()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        if (!empty($this->selectedLoans)) {
            $loans = Loan::whereIn('id', $this->selectedLoans)->where('status', 'pending')->get();
            
            foreach ($loans as $loan) {
                $loan->update(['status' => 'approved']);
            }

            $this->selectedLoans = [];
            $this->selectAll = false;

            Flux::toast(
                text: count($loans) . ' peminjaman berhasil disetujui!',
                variant: 'success'
            );
        }
    }

    public function confirmReject($id)
    {
        $this->loanId = $id;
    }

    public function reject()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        if ($this->loanId) {
            $loan = Loan::with('items.item')->find($this->loanId);

            if ($loan) {
                // Change status to 'rejected'
                $loan->update(['status' => 'rejected']);

                // Restore stock for each item
                foreach ($loan->items as $loanItem) {
                    if ($loanItem->item) {
                        $loanItem->item->increment('stock', $loanItem->quantity);
                    }
                }

                // Reset ID
                $this->loanId = null;

                // Close modal
                $this->dispatch('modal-close', name: 'reject-loan');

                // Show toast
                Flux::toast(
                    text: 'Data peminjaman berhasil ditolak dan stok dikembalikan!',
                    variant: 'danger'
                );
            }
        }
    }

    public function confirmReturn($id)
    {
        $this->loanId = $id;
    }

    public function returnLoan()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        if ($this->loanId) {
            $loan = Loan::with('items.item')->find($this->loanId);

            if ($loan && $loan->status === 'approved') {
                // Change status to 'returned'
                $loan->update(['status' => 'returned']);

                // Restore stock
                foreach ($loan->items as $loanItem) {
                    if ($loanItem->item) {
                        $loanItem->item->increment('stock', $loanItem->quantity);
                    }
                }

                // Reset ID
                $this->loanId = null;

                // Close modal
                $this->dispatch('modal-close', name: 'return-loan');

                // Show toast
                Flux::toast(
                    text: 'Barang berhasil dikembalikan dan stok telah bertambah!',
                    variant: 'success'
                );
            }
        }
    }

    public function render()
    {
        // Hanya tampilkan yang pending
        $query = Loan::with('items.item')->where('status', 'pending')->latest();
        
        if (auth()->user()->isCustomer()) {
            $query->where('user_id', auth()->id());
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

        return view('livewire.loan.index', [
            'loans' => $query->paginate(5),
            'users' => $users
        ]);
    }
}

