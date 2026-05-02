<div>
    <flux:breadcrumbs class="bg-zinc-900 p-2 rounded-md">
        <flux:breadcrumbs.item href="#">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Loan</flux:breadcrumbs.item>
    </flux:breadcrumbs>


    <div class="mt-5 bg-zinc-900 rounded-md p-2 shadow">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h1 class="text-3xl">{{ __('Loan') }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari barang, alasan..." class="w-48 sm:w-64" size="sm" />
                
                <flux:modal.trigger name="filter-modal">
                    <flux:button icon="funnel" variant="ghost" size="sm">Filter Status</flux:button>
                </flux:modal.trigger>
                @if(auth()->user()->isAdmin() && count($selectedLoans) > 0)
                    <flux:button variant="primary" icon="check-circle" wire:click="bulkApprove" size="sm">
                        Approve ({{ count($selectedLoans) }})
                    </flux:button>
                @endif
                <flux:button href="/loan/create" size="sm">Tambah Loan</flux:button>
            </div>
        </div>

        <flux:table class="mt-5">
            <flux:table.columns>
                @if(auth()->user()->isAdmin())
                    <flux:table.column>
                        <flux:checkbox wire:model.live="selectAll" />
                    </flux:table.column>
                @endif
                <flux:table.column>Tanggal Pengembalian</flux:table.column>
                <flux:table.column>Barang & Qty</flux:table.column>
                <flux:table.column>Alasan</flux:table.column>
                <flux:table.column>Durasi</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($loans as $loan)
                    <flux:table.row class="hover:bg-zinc-800 transition-all duration-300 ease-in-out">
                        @if(auth()->user()->isAdmin())
                            <flux:table.cell>
                                @if($loan->status === 'pending')
                                    <flux:checkbox wire:model.live="selectedLoans" value="{{ $loan->id }}" />
                                @endif
                            </flux:table.cell>
                        @endif
                        <flux:table.cell>{{ $loan->loan_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($loan->items->count() > 0)
                                {{-- Ambil nama barang pertama --}}
                                {{ $loan->items->first()->item->name }} - {{ $loan->items->first()->quantity }} pcs

                                {{-- Jika ada barang tambahan, tampilkan (+ jumlah sisa) --}}
                                @if ($loan->items->count() > 1)
                                    <span class="text-gray-500 text-sm">
                                        (+{{ $loan->items->count() - 1 }})
                                    </span>
                                @endif
                            @else
                                <span class="text-red-500 italic">Tidak ada barang</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="max-w-xs truncate">{{ $loan->reason }}</flux:table.cell>
                        <flux:table.cell>
                            @if($loan->loan_date)
                                {{ \Carbon\Carbon::parse($loan->created_at)->startOfDay()->diffInDays(\Carbon\Carbon::parse($loan->loan_date)->startOfDay()) }} Hari
                            @else
                                -
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ ucfirst($loan->status) }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                    <flux:modal.trigger name="view-loan">
                                        <flux:button icon="eye" variant="ghost" wire:click="view({{ $loan->id }})" />
                                    </flux:modal.trigger>
                                    @if(auth()->user()->isAdmin())
                                        @if ($loan->status === 'pending')
                                            <flux:button href="/loan/edit/{{ $loan->id }}" icon="pencil" variant="ghost" />
                                            <flux:modal.trigger name="approve-loan">
                                                <flux:button icon="check" variant="primary" wire:click="confirmApprove({{ $loan->id }})" />
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="reject-loan">
                                                <flux:button icon="x-mark" variant="danger" wire:click="confirmReject({{ $loan->id }})" />
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="delete-loan">
                                                <flux:button icon="trash" variant="danger" wire:click="confirmDelete({{ $loan->id }})" />
                                            </flux:modal.trigger>
                                        @endif
                                        
                                        @if ($loan->status === 'approved')
                                            <flux:modal.trigger name="return-loan">
                                                <flux:button icon="arrow-uturn-left" variant="primary" wire:click="confirmReturn({{ $loan->id }})" />
                                            </flux:modal.trigger>
                                        @endif
                                    @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ auth()->user()->isAdmin() ? '7' : '6' }}" class="text-center text-gray-500 py-4">
                            No loan data available
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
                    <flux:text class="mt-2">
                        Informasi lengkap mengenai peminjaman barang.
                    </flux:text>
                </div>
                <flux:separator />
                <div class="space-y-4">
                    <flux:description>
                        <div class="font-bold text-gray-400">Tanggal Pengembalian</div>
                        <div class="text-white">{{ $selectedLoan->loan_date->format('d M Y') }}</div>
                    </flux:description>
                    <flux:description>
                        <div class="font-bold text-gray-400">Durasi</div>
                        <div class="text-white">
                            @if($selectedLoan->loan_date)
                                {{ \Carbon\Carbon::parse($selectedLoan->created_at)->startOfDay()->diffInDays(\Carbon\Carbon::parse($selectedLoan->loan_date)->startOfDay()) }} Hari
                            @else
                                -
                            @endif
                        </div>
                    </flux:description> 
                    <flux:description>
                        <div class="font-bold text-gray-400">Barang & Qty</div>
                        <div class="bg-zinc-900 p-2 rounded-md">
                            @foreach ($selectedLoan->items as $item)
                                <div class="flex justify-between mt-2">
                                    <div class="text-white">{{ $item->item->name }}</div>
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

    {{-- Modal delete loan --}}
    <flux:modal name="delete-loan" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus Loan?</flux:heading>
                <flux:text class="mt-2">
                    Apakah anda yakin akan menghapus data peminjaman ini?<br>
                    Tindakan ini tidak dapat dibatalkan.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="delete" wire:loading.attr="disabled">
                    Hapus Loan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal approve loan --}}
    <flux:modal name="approve-loan" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Approve Loan?</flux:heading>
                <flux:text class="mt-2">
                    Apakah anda yakin akan menyetujui data peminjaman ini?
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:click="approve" wire:loading.attr="disabled">
                    Approve Loan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal return loan --}}
    <flux:modal name="return-loan" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Kembalikan Barang?</flux:heading>
                <flux:text class="mt-2">
                    Apakah Anda yakin barang-barang dalam peminjaman ini sudah dikembalikan? Stok barang akan otomatis bertambah ke sistem.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:click="returnLoan" wire:loading.attr="disabled">
                    Konfirmasi Pengembalian
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal reject loan --}}
    <flux:modal name="reject-loan" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Peminjaman?</flux:heading>
                <flux:text class="mt-2">
                    Apakah anda yakin akan menolak data peminjaman ini? Stok barang akan otomatis dikembalikan ke sistem.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" wire:click="reject" wire:loading.attr="disabled">
                    Tolak Loan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Filter --}}
    <flux:modal name="filter-modal" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Filter Status Peminjaman</flux:heading>
                <flux:text class="mt-2">
                    Pilih satu atau lebih status untuk memfilter data.
                </flux:text>
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
                <flux:checkbox wire:model.live="statusFilter" value="pending" label="Pending" />
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
