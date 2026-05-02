<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Category</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-5 bg-zinc-900 rounded-md p-5 shadow">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl">{{ __('Kategori') }}</h1>
            <flux:button href="/category/create">Tambah Kategori</flux:button>
        </div>

        <flux:table class="mt-5">
            <flux:table.columns>
                <flux:table.column>Nama Kategori</flux:table.column>
                <flux:table.column>Deskripsi</flux:table.column>
                <flux:table.column>Jumlah Barang</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categories as $category)
                    <flux:table.row class="hover:bg-zinc-800 transition-all duration-300 ease-in-out">
                        <flux:table.cell>{{ $category->name }}</flux:table.cell>
                        <flux:table.cell>{{ $category->description ?? '-' }}</flux:table.cell>
                        <flux:table.cell>{{ $category->items_count }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button href="/category/edit/{{ $category->id }}" icon="pencil" variant="ghost" />
                                <flux:modal.trigger name="delete-category">
                                    <flux:button icon="trash" variant="danger" wire:click="confirmDelete({{ $category->id }})" />
                                </flux:modal.trigger>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-gray-500 py-4">
                            Belum ada data kategori.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>

    {{-- Modal Delete Category --}}
    <flux:modal name="delete-category" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Kategori?</flux:heading>
                <flux:text class="mt-2">
                    Apakah Anda yakin ingin menghapus kategori ini? 
                    Kategori tidak dapat dihapus jika masih digunakan oleh barang.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="delete" wire:loading.attr="disabled">
                    Hapus Kategori
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
