<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="/category">Category</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-5 bg-zinc-900 rounded-md p-5 shadow">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl">{{ __('Tambah Kategori') }}</h1>
        </div>

        <form wire:submit="save" class="mt-5 space-y-5">
            <flux:field>
                <flux:label>Nama Kategori</flux:label>
                <flux:input wire:model="name" type="text" placeholder="Masukkan nama kategori" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Deskripsi (Opsional)</flux:label>
                <flux:textarea wire:model="description" rows="3" placeholder="Masukkan deskripsi singkat..." />
                <flux:error name="description" />
            </flux:field>

            <div class="flex gap-2 pt-2">
                <flux:button type="submit" variant="primary">Simpan</flux:button>
                <flux:button wire:navigate href="/category" variant="ghost">Batal</flux:button>
            </div>
        </form>
    </div>
</div>
