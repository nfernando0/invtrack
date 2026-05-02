<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="/loan">Loan</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-5 bg-zinc-900 rounded-md p-5 shadow">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl">{{ __('Loan') }}</h1>
        </div>

        <form wire:submit="save">
            @foreach ($selectedItems as $index => $row)
                <div class="flex gap-3 mt-5">
                    <flux:select label="Barang" wire:model.live="selectedItems.{{ $index }}.item_id">
                        <option value="">Pilih Barang</option>
                        @foreach ($items as $item)
                            @php
                                // Akses menggunakan $this->namaProperty
                                $isTaken =
                                    in_array($item->id, $this->selectedItemsProperty) &&
                                    $item->id != $selectedItems[$index]['item_id'];
                            @endphp

                            @if (!$isTaken)
                                <option value="{{ $item->id }}">
                                    {{ $item->name }}
                                </option>
                            @endif
                        @endforeach
                    </flux:select>

                    <div>
                        <flux:input label="Qty" type="number"
                            wire:model.live="selectedItems.{{ $index }}.quantity" :max="$row['max_stock']" />
                        @if (isset($row['item_id']) && $row['item_id'] !== '' && isset($row['quantity']) && isset($row['max_stock']) && (int)$row['quantity'] > (int)$row['max_stock'])
                            <div class="text-red-500 text-sm mt-1">jumlah stock melebihi stock</div>
                        @endif
                    </div>

                    @if (count($selectedItems) > 1)
                        <flux:button icon="trash" class="mt-7" variant="danger"
                            wire:click="removeItem({{ $index }})" />
                    @endif
                </div>
            @endforeach


            {{-- Untuk tombol tambah --}}
            <flux:button class="mt-5" wire:click="addItem" :disabled="!$this->canAddMoreProperty">
                Tambah Barang
            </flux:button>

            <flux:field class="mt-5">
                <flux:label>Alasan</flux:label>
                <flux:input wire:model="reason" type="reason" />
                <flux:error name="reason" />
            </flux:field>

            <flux:field class="mt-5">
                <flux:label>Note</flux:label>
                <flux:input wire:model="note" type="note" />
                <flux:error name="note" />
            </flux:field>

            <flux:field class="mt-5">
                <flux:label>Tanggal Pengembalian</flux:label>
                <flux:input wire:model="loan_date" type="date" min="{{ date('Y-m-d') }}" />
                <flux:error name="loan_date" />
            </flux:field>

            <flux:button wire:click="save" type="submit" class="mt-5" :disabled="$this->hasStockErrorProperty">Simpan</flux:button>
            <flux:button wire:navigate href="/loan" variant="ghost">Batal</flux:button>
        </form>

    </div>

</div>
