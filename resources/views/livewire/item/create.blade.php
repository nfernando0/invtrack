<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="/item">Item</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
    </flux:breadcrumbs>


    <div class="mt-5 bg-zinc-900 rounded-md p-4 shadow">
        <div class="md:w-1/4 w-full">
            <form wire:submit="save">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input wire:model="name" type="name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field class="mt-5">
                    <flux:label>Stock</flux:label>
                    <flux:input wire:model="stock" type="number" />
                    <flux:error name="stock" />
                </flux:field>

                <flux:field class="mt-5">
                    <flux:label>Image</flux:label>
                    <flux:input wire:model="image" type="file" accept="image/*" />
                    <flux:error name="image" />
                    @if ($image)
                        <div x-data="{ open: false }" class="mt-2">
                            <div @click="open = true" class="rounded-md overflow-hidden max-h-32 inline-block cursor-pointer   transition">
                                <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover">
                            </div>
                            <template x-teleport="body">
                                <dialog x-ref="dialog" x-init="$watch('open', val => val ? $refs.dialog.showModal() : $refs.dialog.close())" @click="open = false" @close="open = false" class="bg-transparent p-0 m-0 max-w-none max-h-none w-screen h-screen backdrop:bg-black/90 outline-none">
                                    <div class="w-full h-full flex items-center justify-center cursor-zoom-out">
                                        <img src="{{ $image->temporaryUrl() }}" class="max-w-[95vw] max-h-[95vh] object-contain rounded-md shadow-2xl">
                                    </div>
                                </dialog>
                            </template>
                        </div>
                    @endif
                </flux:field>


                <flux:button wire:click="save" type="submit" class="mt-5">Simpan</flux:button>
                <flux:button wire:navigate href="/item" variant="outline">Cancel</flux:button>
            </form>
        </div>
    </div>
</div>
