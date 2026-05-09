<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="/item">Item</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
    </flux:breadcrumbs>


    <div class="mt-5 bg-zinc-900 rounded-md p-4 shadow">
        <div class="md:w-1/4 w-full">
            <form wire:submit="update" class="mt-6 space-y-4">
                <flux:input label="Nama Barang" wire:model="name" />
                <flux:field>
                    <flux:label>Kategori</flux:label>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($categories as $category)
                            <flux:checkbox wire:model="category_ids" value="{{ $category->id }}" label="{{ $category->name }}" />
                        @endforeach
                    </div>
                    <flux:error name="category_ids" />
                </flux:field>
                <flux:input label="Stok" type="number" wire:model="stock" />
                <flux:field>
                    <flux:label>Gambar Barang</flux:label>
                    <flux:input wire:model="newImage" type="file" accept="image/*" />
                    <flux:error name="newImage" />
                    <div class="mt-2 flex gap-4">
                        @if ($newImage)
                            <div x-data="{ open: false }">
                                <span class="text-sm text-gray-500 block mb-1">Preview Baru:</span>
                                <div @click="open = true" class="rounded-md overflow-hidden max-h-32 inline-block cursor-pointer   transition">
                                    <img src="{{ $newImage->temporaryUrl() }}" class="h-full w-full object-cover">
                                </div>
                                <template x-teleport="body">
                                    <dialog x-ref="dialog" x-init="$watch('open', val => val ? $refs.dialog.showModal() : $refs.dialog.close())" @click="open = false" @close="open = false" class="bg-transparent p-0 m-0 max-w-none max-h-none w-screen h-screen backdrop:bg-black/90 outline-none">
                                        <div class="w-full h-full flex items-center justify-center cursor-zoom-out">
                                            <img src="{{ $newImage->temporaryUrl() }}" class="max-w-[95vw] max-h-[95vh] object-contain rounded-md shadow-2xl">
                                        </div>
                                    </dialog>
                                </template>
                            </div>
                        @elseif ($currentImage)
                            <div x-data="{ open: false }">
                                <span class="text-sm text-gray-500 block mb-1">Gambar Saat Ini:</span>
                                <div @click="open = true" class="rounded-md overflow-hidden max-h-32 inline-block cursor-pointer   transition">
                                    <img src="{{ asset('storage/' . $currentImage) }}" class="h-full w-full object-cover">
                                </div>
                                <template x-teleport="body">
                                    <dialog x-ref="dialog" x-init="$watch('open', val => val ? $refs.dialog.showModal() : $refs.dialog.close())" @click="open = false" @close="open = false" class="bg-transparent p-0 m-0 max-w-none max-h-none w-screen h-screen backdrop:bg-black/90 outline-none">
                                        <div class="w-full h-full flex items-center justify-center cursor-zoom-out">
                                            <img src="{{ asset('storage/' . $currentImage) }}" class="max-w-[95vw] max-h-[95vh] object-contain rounded-md shadow-2xl">
                                        </div>
                                    </dialog>
                                </template>
                            </div>
                        @endif
                    </div>
                </flux:field>
                <div class="flex gap-2 mt-4">
                    <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
                    <flux:button variant="ghost" href="/item">Batal</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
