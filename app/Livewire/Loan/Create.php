<?php

namespace App\Livewire\Loan;

use App\Models\Item;
use App\Models\Loan;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{

    public $selectedItems = []; // Menyimpan daftar item yang akan dipinjam
    public $stockLimit = 0;
    public $note = '';
    public $reason = '';
    public $quantity = '';
    public $loan_date;
    public $items;

    public function addItem()
    {
        // Tambahkan item_id, quantity, dan max_stock untuk validasi per baris
        $this->selectedItems[] = [
            'item_id' => '',
            'quantity' => 1,
            'max_stock' => 0
        ];
    }

    #[Computed()]
    public function selectedItemsProperty()
    {
        return collect($this->selectedItems)->pluck('item_id')->filter()->all();
    }

    #[Computed]
    public function canAddMoreProperty()
    {
        // Menggunakan perbandingan jumlah baris vs jumlah total item
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
        // Mendapatkan indeks dan field yang diubah (misal: selectedItems.0.item_id)
        if (str_contains($key, 'item_id')) {
            $index = explode('.', $key)[0];
            $item = \App\Models\Item::find($value);

            // Update batas stok untuk baris yang spesifik tersebut
            $this->selectedItems[$index]['max_stock'] = $item ? $item->stock : 0;

            // Reset quantity ke 1 jika barang berubah untuk menghindari overflow stok lama
            $this->selectedItems[$index]['quantity'] = 1;
        }
    }
    public function mount()
    {
        // Inisialisasi baris pertama saat halaman dimuat
        $this->addItem();
        $this->items = \App\Models\Item::where('stock', '>', 0)->get();
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
    }

    public function save()
    {
        // 1. Validasi dinamis untuk semua baris barang
        $rules = [
            'selectedItems.*.item_id' => 'required|exists:items,id',
            'selectedItems.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $itemId = $this->selectedItems[$index]['item_id'] ?? null;
                    if ($itemId) {
                        $item = \App\Models\Item::find($itemId);
                        if ($item && $value > $item->stock) {
                            $fail("Kuantitas untuk barang '{$item->name}' melebihi stok tersedia (Sisa: {$item->stock}).");
                        }
                    }
                },
            ],
            'note' => 'required',
            'reason' => 'required',
            'loan_date' => 'required|date|after_or_equal:today',
        ];

        // Tambahkan validasi stok manual di dalam loop jika diperlukan,
        // atau gunakan validasi max yang sudah kita buat sebelumnya.
        $this->validate($rules);

        try {
            DB::transaction(function () {
                // 2. Simpan Header Peminjaman
                $loan = Loan::create([
                    'user_id' => auth()->id(),
                    'status' => 'pending', // Status awal
                    'reason' => $this->reason, // Status awal
                    'note' => $this->note, // Status awal
                    'loan_date' => $this->loan_date,
                ]);

                // 3. Simpan Detail Barang yang dipinjam
                foreach ($this->selectedItems as $item) {
                    $loan->items()->create([
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],
                    ]);

                    // Opsional: Jika ingin stok langsung berkurang saat ajukan (bukan saat approve)
                    \App\Models\Item::find($item['item_id'])->decrement('stock', $item['quantity']);
                }
            });

            // 4. Feedback Sukses
            Flux::toast(
                text: 'Pengajuan peminjaman berhasil dikirim!',
                variant: 'success',
                heading: 'Berhasil'
            );

            // 5. Reset Form dan arahkan ke halaman daftar pinjaman
            $this->reset('selectedItems');
            return redirect()->route('loan.index');
        } catch (\Exception $e) {
            Flux::toast(
                text: 'Error: ' . $e->getMessage(),
                variant: 'danger'
            );
        }
    }

    public function render()
    {
        return view('livewire.loan.create');
    }
}
