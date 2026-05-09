<?php

namespace App\Livewire\Item;

use App\Models\Item;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $name; // Properti untuk wire:model="name"
    public $stock; // Properti untuk wire:model="stock"
    public $item;
    public $newImage;
    public $currentImage;
    public $category_ids = [];

    public function mount(Item $item)
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        $this->item = $item;

        // ISI DATA DI SINI AGAR FORM TIDAK KOSONG
        $this->name = $item->name;
        $this->stock = $item->stock;
        $this->currentImage = $item->image;
        $this->category_ids = $item->categories->pluck('id')->toArray();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'newImage' => 'nullable|image|max:2048',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $data = [
            'name' => $this->name,
            'stock' => $this->stock,
        ];

        if ($this->newImage) {
            $data['image'] = $this->newImage->store('items', 'public');
        }

        $this->item->update($data);
        $this->item->categories()->sync($this->category_ids);

        session()->flash('message', 'Barang berhasil diperbaharui!');
        return redirect()->to('/item');
    }

    public function render()
    {
        return view('livewire.item.edit', [
            'item' => $this->item,
            'categories' => \App\Models\Category::orderBy('name')->get()
        ]);
    }
}
