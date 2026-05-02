<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable|string')]
    public $description;

    public function mount()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');
    }

    public function save()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Kategori berhasil ditambahkan!');

        return redirect()->to('/category');
    }

    public function render()
    {
        return view('livewire.category.create');
    }
}
