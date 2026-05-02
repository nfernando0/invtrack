<?php

namespace App\Livewire\Loan;

use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanItem;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{

    public $loan_id;
    public $loan;
    public $selectedItems = [];
    public $items;
    public $reason;
    public $note;
    public $loan_date;

    public function mount(Loan $loan)
    {
        if ($loan->status !== 'pending') {
            abort(403, 'Peminjaman ini sudah disetujui dan tidak dapat diedit lagi.');
        }

        $this->loan_id = $loan->id;
        $this->loan = $loan;
        $this->selectedItems = $loan->items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                // Stok maksimal adalah stok saat ini ditambah kuantitas yang sedang dipinjam
                'max_stock' => $item->item->stock + $item->quantity,
            ];
        })->toArray();
        $this->reason = $loan->reason;
        $this->note = $loan->note;
        $this->loan_date = $loan->loan_date ? $loan->loan_date->format('Y-m-d') : null;
        
        // Memuat semua barang yang stoknya lebih dari 0 ATAU barang yang sudah telanjur dipinjam
        $this->items = Item::where('stock', '>', 0)->orWhereIn('id', array_column($this->selectedItems, 'item_id'))->get();
    }

    #[Computed()]
    public function selectedItemsProperty()
    {
        return collect($this->selectedItems)->pluck('item_id')->filter()->all();
    }

    #[Computed]
    public function canAddMoreProperty()
    {
        return count($this->selectedItems) < $this->items->count();
    }

    #[Computed]
    public function hasStockErrorProperty()
    {
        foreach ($this->selectedItems as $item) {
            if (!empty($item['item_id']) && isset($item['quantity']) && isset($item['max_stock'])) {
                if ((int)$item['quantity'] > (int)$item['max_stock']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function updatedSelectedItems($value, $key)
    {
        if (str_contains($key, 'item_id')) {
            $index = explode('.', $key)[0];
            $item = Item::find($value);
            
            // Cek apakah barang ini adalah barang yang sejak awal ada di pinjaman ini
            $originalQuantity = 0;
            if ($this->loan) {
                $originalLoanItem = $this->loan->items->where('item_id', $value)->first();
                if ($originalLoanItem) {
                    $originalQuantity = $originalLoanItem->quantity;
                }
            }
            
            $this->selectedItems[$index]['max_stock'] = $item ? ($item->stock + $originalQuantity) : 0;
            $this->selectedItems[$index]['quantity'] = 1;
        }
    }

    public function addItem()
    {
        $this->selectedItems[] = [
            'item_id' => null,
            'quantity' => 1,
            'max_stock' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
    }

    public function save()
    {
        $this->validate([
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*.item_id' => 'required|exists:items,id',
            'selectedItems.*.quantity' => 'required|integer|min:1',
            'reason' => 'required',
            'note' => 'required',
            'loan_date' => 'required|date|after_or_equal:today',
        ]);

        $loan = Loan::findOrFail($this->loan_id);
        
        // 1. Kembalikan stok lama sebelum menghapus relasinya
        foreach ($loan->items as $oldItem) {
            if ($oldItem->item) {
                $oldItem->item->increment('stock', $oldItem->quantity);
            }
        }
        $loan->items()->delete();

        // 2. Update data peminjaman utama
        $loan->update([
            'reason' => $this->reason,
            'note' => $this->note,
            'loan_date' => $this->loan_date,
        ]);

        // 3. Simpan relasi peminjaman baru dan kurangi stok
        foreach ($this->selectedItems as $item) {
            LoanItem::create([
                'loan_id' => $loan->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
            ]);
            
            $dbItem = Item::find($item['item_id']);
            if ($dbItem) {
                $dbItem->decrement('stock', $item['quantity']);
            }
        }

        Flux::toast(
            text: 'Data peminjaman berhasil diperbarui!',
            variant: 'success'
        );
        $this->redirect('/loan');
    }

    public function render()
    {
        return view('livewire.loan.edit');
    }
}
