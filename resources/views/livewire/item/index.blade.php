<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Item</flux:breadcrumbs.item>
    </flux:breadcrumbs>


    <div class="mt-5 bg-zinc-900 rounded-md p-2 shadow">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl">{{ __('Item') }}</h1>
            <flux:button href="/item/create">Tambah Barang</flux:button>
        </div>

        <flux:table class="mt-5">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column>Kategori</flux:table.column>
                <flux:table.column>Stock</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($items as $item)
                    <flux:table.row :key="$item->id" class="hover:bg-zinc-800 transition-all duration-300 ease-in-out">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                @if ($item->image)
                                    <div x-data="{ open: false }">
                                        <div @click="open = true" class="w-10 h-10 rounded-md overflow-hidden shrink-0 cursor-pointer hover:ring-2 hover:ring-indigo-500 transition">
                                            <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover">
                                        </div>
                                        <template x-teleport="body">
                                            <dialog x-ref="dialog" x-init="$watch('open', val => val ? $refs.dialog.showModal() : $refs.dialog.close())" @click="open = false" @close="open = false" class="bg-transparent p-0 m-0 max-w-none max-h-none w-screen h-screen backdrop:bg-black/90 outline-none">
                                                <div class="w-full h-full flex items-center justify-center cursor-zoom-out">
                                                    <img src="{{ asset('storage/' . $item->image) }}" class="max-w-[95vw] max-h-[95vh] object-contain rounded-md shadow-2xl">
                                                </div>
                                            </dialog>
                                        </template>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-md bg-zinc-800 flex items-center justify-center">
                                        <flux:icon.photo class="w-5 h-5 text-zinc-500" />
                                    </div>
                                @endif
                                <span>{{ $item->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($item->category)
                                <flux:badge size="sm" color="zinc">{{ $item->category->name }}</flux:badge>
                            @else
                                <span class="text-gray-500 italic text-sm">Tidak ada</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($item->stock == 0)
                                <span class="text-red-500">Sold Out</span>
                            @elseif($item->stock < 5)
                                <span class="text-yellow-500">{{ $item->stock }} pcs</span>
                            @else
                                {{ $item->stock }} pcs
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button href="/item/edit/{{ $item->id }}" icon="pencil-square" variant="primary">
                                    Edit</flux:button>
                                <flux:modal.trigger name="view-item">
                                    <flux:button icon="eye" wire:click="view({{ $item->id }})">View
                                    </flux:button>
                                </flux:modal.trigger>
                                <flux:modal.trigger name="delete-profile">
                                    <flux:button icon="trash" variant="danger"
                                        wire:click="confirmDelete({{ $item->id }})">
                                        Delete
                                    </flux:button>
                                </flux:modal.trigger>


                            </div>
                        </flux:table.cell>
                    </flux:table.row>


                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="text-center">Tidak ada item</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <flux:pagination :paginator="$items" />
    </div>

    {{-- Modal Delete --}}
    <flux:modal name="delete-profile" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Barang?</flux:heading>
                <flux:text class="mt-2">
                    Apakah anda yang akan menghapus barang ini.<br>
                    Tindakan ini tidak dapat dibatalkan.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="delete" wire:loading.attr="disabled">
                    Hapus Barang
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal View --}}
    <flux:modal name="view-item" class="md:w-[450px]">
        <div class="space-y-6">
            @if ($selectedItem)
                <div>
                    <flux:heading size="lg">Detail Barang</flux:heading>
                    <flux:subheading>Informasi lengkap mengenai aset inventaris.</flux:subheading>
                </div>

                <flux:separator />

                <div class="space-y-4">
                    <flux:description>
                        <div class="font-bold text-gray-400">Nama Barang:</div>
                        <div class="text-white">{{ $selectedItem->name }}</div>
                    </flux:description>

                    @if ($selectedItem->image)
                        <flux:description>
                            <div class="font-bold text-gray-400">Gambar:</div>
                            <div x-data="{ open: false }" class="mt-2">
                                <div @click="open = true" class="rounded-md overflow-hidden max-h-48 inline-block cursor-pointer   transition">
                                    <img src="{{ asset('storage/' . $selectedItem->image) }}" class="h-full w-full object-cover">
                                </div>
                                <template x-teleport="body">
                                    <dialog x-ref="dialog" x-init="$watch('open', val => val ? $refs.dialog.showModal() : $refs.dialog.close())" @click="open = false" @close="open = false" class="bg-transparent p-0 m-0 max-w-none max-h-none w-screen h-screen backdrop:bg-black/90 outline-none">
                                        <div class="w-full h-full flex items-center justify-center cursor-zoom-out">
                                            <img src="{{ asset('storage/' . $selectedItem->image) }}" class="max-w-[95vw] max-h-[95vh] object-contain rounded-md shadow-2xl">
                                        </div>
                                    </dialog>
                                </template>
                            </div>
                        </flux:description>
                    @endif

                    <flux:description>
                        <div class="font-bold text-gray-400">Stok Tersedia:</div>
                        <div class="text-white">{{ $selectedItem->stock }} unit</div>
                    </flux:description>

                    <flux:description>
                        <div class="font-bold text-gray-400">Terakhir Diperbarui:</div>
                        <div class="text-white">{{ $selectedItem->updated_at->format('d M Y, H:i') }}</div>
                    </flux:description>
                </div>
            @else
                <div class="flex justify-center p-4">
                    <flux:spacer />
                    <span>Memuat data...</span>
                </div>
            @endif

            <div class="flex">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button>Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>


    @if (session()->has('message'))
        <script>
            // Jika menggunakan Flux Toast secara manual via JS
            window.onload = () => {
                Flux.toast({
                    text: "{{ session('message') }}",
                    variant: 'success'
                });
            };
        </script>
    @endif
</div>
