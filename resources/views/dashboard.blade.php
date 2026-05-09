<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Alerts Section --}}
        @if(auth()->user()->isAdmin())
            @if($pendingLoansCount > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                        <flux:icon.clock class="w-5 h-5 shrink-0" />
                        <div>
                            <span class="font-medium">Terdapat {{ $pendingLoansCount }} peminjaman menunggu persetujuan.</span>
                            <span class="text-sm opacity-80 ml-1 hidden sm:inline">Harap segera diproses.</span>
                        </div>
                    </div>
                    <flux:button size="sm" variant="ghost" class="text-yellow-800 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 shrink-0" href="{{ route('loan.index') }}" wire:navigate>Lihat</flux:button>
                </div>
            @endif

            @if($lowStockItemsCount > 0)
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-red-800 dark:text-red-300">
                        <flux:icon.exclamation-triangle class="w-5 h-5 shrink-0" />
                        <div>
                            <span class="font-medium">Terdapat {{ $lowStockItemsCount }} barang dengan stok menipis (<= 5).</span>
                            <span class="text-sm opacity-80 ml-1 hidden sm:inline">Pertimbangkan untuk menambah stok.</span>
                        </div>
                    </div>
                    <flux:button size="sm" variant="ghost" class="text-red-800 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 shrink-0" href="{{ route('item.index') }}" wire:navigate>Lihat</flux:button>
                </div>
            @endif
        @else
            @if($overdueLoansCount > 0)
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-red-800 dark:text-red-300">
                        <flux:icon.exclamation-circle class="w-5 h-5 shrink-0" />
                        <div>
                            <span class="font-medium">Anda memiliki {{ $overdueLoansCount }} peminjaman yang terlambat dikembalikan!</span>
                            <span class="text-sm opacity-80 ml-1 hidden sm:inline">Harap segera kembalikan barang.</span>
                        </div>
                    </div>
                    <flux:button size="sm" variant="ghost" class="text-red-800 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 shrink-0" href="{{ route('loan.index') }}" wire:navigate>Lihat</flux:button>
                </div>
            @endif

            @if($upcomingReturnsCount > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-yellow-800 dark:text-yellow-300">
                        <flux:icon.clock class="w-5 h-5 shrink-0" />
                        <div>
                            <span class="font-medium">Anda memiliki {{ $upcomingReturnsCount }} peminjaman yang hampir jatuh tempo.</span>
                            <span class="text-sm opacity-80 ml-1 hidden sm:inline">Pastikan barang dikembalikan tepat waktu.</span>
                        </div>
                    </div>
                    <flux:button size="sm" variant="ghost" class="text-yellow-800 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 shrink-0" href="{{ route('loan.index') }}" wire:navigate>Lihat</flux:button>
                </div>
            @endif
        @endif
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
        <div class="grid gap-4 md:grid-cols-3 flex-1 h-full">
            <div class="order-2 md:order-1 md:col-span-2 relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 flex flex-col">
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

            <div class="order-1 md:order-2 md:col-span-1 relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 flex flex-col">
                <flux:heading size="lg" class="mb-4">Statistik Barang</flux:heading>
                <div class="flex-1 flex items-center justify-center min-h-[300px]" x-data="{
                    init() {
                        let isDark = document.documentElement.classList.contains('dark');
                        let chart = new ApexCharts(this.$refs.chart, {
                            series: [{{ (int) $totalLoanedItems }}, {{ (int) $totalReturnedItems }}, {{ (int) $totalAvailableItems }}],
                            chart: {
                                type: 'donut',
                                height: 320,
                                fontFamily: 'inherit',
                                background: 'transparent'
                            },
                            labels: ['Dipinjam', 'Dikembalikan', 'Tersedia'],
                            colors: ['#eab308', '#3b82f6', '#22c55e'],
                            stroke: {
                                show: true,
                                colors: isDark ? ['#171717'] : ['#ffffff']
                            },
                            theme: {
                                mode: isDark ? 'dark' : 'light'
                            },
                            legend: {
                                position: 'bottom'
                            },
                            dataLabels: {
                                enabled: false
                            }
                        });
                        chart.render();
                        
                        let observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.attributeName === 'class') {
                                    let isDark = document.documentElement.classList.contains('dark');
                                    chart.updateOptions({
                                        theme: { mode: isDark ? 'dark' : 'light' },
                                        stroke: { colors: isDark ? ['#171717'] : ['#ffffff'] }
                                    });
                                }
                            });
                        });
                        observer.observe(document.documentElement, { attributes: true });
                    }
                }">
                    <div x-ref="chart" class="w-full"></div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 flex-1">
            <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-6 flex flex-col">
                <flux:heading size="lg" class="mb-4">Barang Terpopuler</flux:heading>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Nama Barang</flux:table.column>
                        <flux:table.column>Kategori</flux:table.column>
                        <flux:table.column>Total Dipinjam</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($popularItems as $item)
                            <flux:table.row>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        @if ($item->image)
                                            <div class="w-8 h-8 rounded-md overflow-hidden shrink-0">
                                                <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-md bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                                                <flux:icon.photo class="w-4 h-4 text-zinc-500" />
                                            </div>
                                        @endif
                                        <span class="font-medium">{{ $item->name }}</span>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($item->categories && $item->categories->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($item->categories as $category)
                                                <flux:badge size="sm" color="zinc">{{ $category->name }}</flux:badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 italic text-sm">Tidak ada</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="blue" size="sm" inset="top bottom">
                                        {{ $item->total_borrowed }} pcs
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="3" class="text-center text-gray-500 py-4">
                                    Belum ada data peminjaman barang.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </div>
</x-layouts::app>
