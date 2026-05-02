<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 flex flex-col items-center justify-center p-6">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Barang Dipinjam</span>
                <span class="text-4xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalLoanedItems }}</span>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 flex flex-col items-center justify-center p-6">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Barang Dikembalikan</span>
                <span class="text-4xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalReturnedItems }}</span>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 flex flex-col items-center justify-center p-6">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Total Barang Tersedia</span>
                <span class="text-4xl font-bold text-neutral-900 dark:text-white mt-2">{{ $totalAvailableItems }}</span>
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 flex flex-col">
            <flux:heading size="lg" class="mb-4">Peminjaman Terbaru</flux:heading>
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Peminjam</flux:table.column>
                    <flux:table.column>Barang</flux:table.column>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($recentLoans as $loan)
                        <flux:table.row>
                            <flux:table.cell>{{ $loan->user?->name ?? 'Unknown' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($loan->items->count() > 0)
                                    <flux:tooltip content="{{ $loan->items->map(fn($i) => $i->item->name . ' (' . $i->quantity . ' pcs)')->join(', ') }}">
                                        <div class="cursor-help inline-flex items-center gap-1">
                                            {{ $loan->items->first()->item->name }} ({{ $loan->items->first()->quantity }})
                                            @if ($loan->items->count() > 1)
                                                <span class="text-gray-500 text-xs">(+{{ $loan->items->count() - 1 }})</span>
                                            @endif
                                        </div>
                                    </flux:tooltip>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $loan->loan_date ? $loan->loan_date->format('d M Y') : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="{{ match($loan->status) { 'pending' => 'yellow', 'approved' => 'green', 'returned' => 'blue', 'rejected' => 'red', default => 'zinc' } }}" size="sm" inset="top bottom">
                                    {{ ucfirst($loan->status) }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-gray-500 py-4">
                                Belum ada transaksi peminjaman.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</x-layouts::app>
