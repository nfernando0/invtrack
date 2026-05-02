<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $categoryId;

    public function mount()
    {
        abort_if(!auth()->user()->isAdmin(), 403, 'Unauthorized action.');
    }

    public function confirmDelete($id)
    {
        $this->categoryId = $id;
    }

    public function delete()
    {
        if ($this->categoryId) {
            $category = Category::find($this->categoryId);
            if ($category) {
                // Jangan hapus jika ada barang yang terhubung
                if ($category->items()->count() > 0) {
                    $this->dispatch('modal-close', name: 'delete-category');
                    Flux::toast(text: 'Kategori tidak bisa dihapus karena sedang digunakan oleh barang!', variant: 'danger');
                    return;
                }

                $category->delete();
                $this->categoryId = null;
                $this->dispatch('modal-close', name: 'delete-category');
                Flux::toast(text: 'Kategori berhasil dihapus!', variant: 'success');
            }
        }
    }

    public function render()
    {
        return view('livewire.category.index', [
            'categories' => Category::withCount('items')->latest()->paginate(5),
        ]);
    }
}
