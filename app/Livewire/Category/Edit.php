<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Component;

class Edit extends Component
{
    public $category;
    public $name;
    public $description;

    public function mount(Category $category)
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');

        $this->category = $category;
        $this->name = $category->name;
        $this->description = $category->description;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->category->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Kategori berhasil diperbaharui!');
        return redirect()->to('/category');
    }

    public function render()
    {
        return view('livewire.category.edit');
    }
}
