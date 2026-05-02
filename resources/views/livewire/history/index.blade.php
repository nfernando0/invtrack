<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>History</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mt-5 bg-zinc-900 rounded-md p-2 shadow">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 class="text-3xl">{{ __('History Peminjaman') }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari barang, alasan..." class="w-48 sm:w-64" size="sm" />
                
                <flux:modal.trigger name="filter-modal">
                    <flux:button icon="funnel" variant="ghost" size="sm">Filter Status</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:table class="mt-5">
            <flux:table.columns>
                <flux:table.column>Tanggal Peminjaman</flux:table.column>
                <flux:table.column>Tenggat Waktu</flux:table.column>
                <flux:table.column>Barang & Qty</flux:table.column>
                <flux:table.column>Alasan</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($loans as $loan)
                    <flux:table.row class="hover:bg-zinc-800 transition-all duration-300 ease-in-out">
                        <flux:table.cell>{{ $loan->created_at->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $loan->loan_date ? $loan->loan_date->format('d M Y') : '-' }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($loan->items->count() > 0)
                                {{ $loan->items->first()->item->name }} - {{ $loan->items->first()->quantity }} pcs
                                @if ($loan->items->count() > 1)
                                    <span class="text-gray-500 text-sm">(+{{ $loan->items->count() - 1 }})</span>
                                @endif
                            @else
                                <span class="text-red-500 italic">Tidak ada barang</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">{{ $loan->reason }}</flux:table.cell>
                        <flux:table.cell>
                            {{ ucfirst($loan->status) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:modal.trigger name="view-loan">
                                    <flux:button icon="eye" variant="ghost" wire:click="view({{ $loan->id }})" />
                                </flux:modal.trigger>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-gray-500 py-4">
                            No history data available
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        <div class="mt-4">
            {{ $loans->links() }}
        </div>
    </div>

    <!-- Modal view loan -->
    <flux:modal name="view-loan" class="min-w-[22rem]">
        <div class="space-y-6">
            @if ($selectedLoan)
                <div>
                    <flux:heading size="lg">Detail Loan</flux:heading>
                    <flux:text class="mt-2">Informasi lengkap mengenai peminjaman barang.</flux:text>
                </div>
                <flux:separator />
                <div class="space-y-4">
                    <flux:description>
                        <div class="font-bold text-gray-400">Tanggal Pengembalian</div>
                        <div class="text-white">{{ $selectedLoan->loan_date->format('d M Y') }}</div>
                    </flux:description>
                    <flux:description>
                        <div class="font-bold text-gray-400">Barang & Qty</div>
                        <div class="bg-zinc-900 p-2 rounded-md">
                            @foreach ($selectedLoan->items as $item)
                                <div class="flex justify-between mt-2">
                                    <div class="text-white">{{ $item->item->name ?? 'Barang Dihapus' }}</div>
                                    <div class="text-white">{{ $item->quantity }} pcs</div>
                                </div>
                            @endforeach 
                        </div>
                    </flux:description>
                    <flux:description>
                        <div class="font-bold text-gray-400">Alasan</div>
                        <div class="text-white">{{ $selectedLoan->reason }}</div>
                    </flux:description>
                    <flux:description>
                        <div class="font-bold text-gray-400">Status</div>
                        <div class="text-white">{{ ucfirst($selectedLoan->status) }}</div>
                    </flux:description>
                </div>
                <flux:separator />
            @else
                <div class="flex justify-center p-4">
                    <span>Memuat data...</span>
                </div>
            @endif
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Filter --}}
    <flux:modal name="filter-modal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Filter History</flux:heading>
                <flux:text class="mt-2">Pilih filter untuk menyesuaikan data history.</flux:text>
            </div>
            
            <div class="space-y-3">
                @if(auth()->user()->isAdmin())
                    <flux:heading size="sm">Peminjam</flux:heading>
                    <flux:select wire:model.live="userFilter">
                        <option value="">Semua Peminjam</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:separator class="my-4" />
                @endif
                
                <flux:heading size="sm">Status</flux:heading>
                <flux:checkbox wire:model.live="statusFilter" value="approved" label="Approved" />
                <flux:checkbox wire:model.live="statusFilter" value="returned" label="Returned" />
                <flux:checkbox wire:model.live="statusFilter" value="rejected" label="Rejected" />
                
                <flux:separator class="my-4" />
                
                <flux:heading size="sm">Peringatan</flux:heading>
                <flux:checkbox wire:model.live="isOverdue" label="Tampilkan Hanya yang Terlambat (Overdue)" />

                <flux:separator class="my-4" />
                
                <flux:heading size="sm">Rentang Tanggal Peminjaman</flux:heading>
                <div class="grid grid-cols-2 gap-2">
                    <flux:input type="date" wire:model.live="dateStart" label="Dari" />
                    <flux:input type="date" wire:model.live="dateEnd" label="Sampai" />
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="clearFilter">Hapus Filter</flux:button>
                <flux:modal.close>
                    <flux:button variant="primary">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
