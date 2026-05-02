<?php

namespace App\Livewire\Item;

use App\Models\Item;
use App\Models\Loan;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|integer|min:1')]
    public $stock;

    #[Validate('nullable|image|max:2048')]
    public $image;

    #[Validate('required|exists:categories,id')]
    public $category_id;

    public function mount()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');
    }

    public function save()
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('items', 'public');
        }

        Item::create([
            'name' => $this->name,
            'stock' => $this->stock,
            'image' => $imagePath,
            'category_id' => $this->category_id,
        ]);

        // Reset input setelah simpan agar form bersih kembali
        $this->reset(['name', 'stock', 'image', 'category_id']);

        // Jika ini di dalam modal, Anda bisa menutupnya di sini
        session()->flash('message', 'Barang berhasil ditambahkan!');

        return redirect()->to('/item');
    }
    public function render()
    {
        return view('livewire.item.create', [
            'categories' => \App\Models\Category::orderBy('name')->get()
        ]);
    }
}
