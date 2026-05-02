<?php

namespace App\Livewire\Item;

use App\Models\Item;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;

    public $itemId;
    public $selectedItem;

    public function mount()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');
    }

    public function confirmDelete($id)
    {
        // Isi properti dengan ID barang yang diklik
        $this->itemId = $id;
    }

    public function delete()
    {
        if ($this->itemId) {
            $item = Item::find($this->itemId);

            if ($item) {
                $item->delete();

                // Reset ID
                $this->itemId = null;

                // Tutup modal sesuai dengan atribut 'name' di Blade Anda
                $this->dispatch('modal-close', name: 'delete-profile');

                // Tampilkan Toast menggunakan Facade Flux
                Flux::toast(
                    text: 'Barang berhasil dihapus!',
                    variant: 'success'
                );
            }
        }
    }

    // Properti untuk menyimpan data barang yang dilihat

    public function view($id)
    {
        // Mengambil data barang dan menyimpannya di properti selectedItem
        $this->selectedItem = \App\Models\Item::find($id);
    }

    public function render()
    {
        return view('livewire.item.index', [
            'items' => Item::with('category')->paginate(5),
        ]);
    }
}
