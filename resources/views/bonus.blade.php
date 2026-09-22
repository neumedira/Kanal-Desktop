<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Bonus - Kanal Kalimantan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js untuk Modal Popup -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between m-0" x-data="{ openModal: false }">

    <!-- Wrapper Utama -->
    <div>
        <!-- Memanggil Komponen Navbar -->
        @include('components.navbar')

        <div class="max-w-7xl mx-auto px-6 py-6">
            <!-- Header Title & Filter Sejajar -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Kelola Bonus</h2>
                    <p class="text-gray-500 text-sm">Manajemen dan rekapitulasi performa pewarta.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Search Bar (Berfungsi dengan Enter / Ketik) -->
                    <div class="relative w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Cari Data..." 
                            class="w-full pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:border-gray-400">
                    </div>

                    <!-- Filter Bulan -->
                    <select id="filter-bulan" class="bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-gray-400">
                        @php $currentMonth = request('bulan', date('m')); @endphp
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Tombol Pengaturan (Trigger Modal) -->
                    <button @click="openModal = true" type="button" class="bg-white border border-gray-300 px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50 transition">
                        <i class="fas fa-cog"></i>
                    </button>
                </div>
            </div>

            <!-- Tabel Data Bonus -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <th class="py-3 px-4 w-16">No</th>
                                <th class="py-3 px-4 w-1/5">Pewarta</th>
                                <th class="py-3 px-4 w-2/5">Judul Berita</th>
                                <th class="py-3 px-4 text-right w-32">Views</th>
                                <th class="py-3 px-4 text-right pr-6 w-36">Bonus (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
                            @forelse($bonuses ?? [] as $index => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <!-- Nomor urut aman dengan pengecekan fungsi pagination -->
                                <td class="py-4 px-4">
                                    {{ (isset($bonuses) && method_exists($bonuses, 'firstItem')) ? $bonuses->firstItem() + $index : $index + 1 }}
                                </td>
                                <td class="py-4 px-4 font-medium">{{ $item->wartawan->nama ?? '-' }}</td>
                                <td class="py-4 px-4">
                                    <div class="text-gray-900 font-normal mb-0.5">{{ $item->artikel->judul ?? '-' }}</div>
                                    <a href="{{ $item->artikel->link ?? '#' }}" target="_blank" class="text-blue-600 text-xs hover:underline">
                                        {{ Str::limit($item->artikel->link ?? '-', 55) }}
                                    </a>
                                </td>
                                <td class="py-4 px-4 text-right">{{ number_format($item->views_saat_dihitung ?? 0, 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-right pr-6 font-bold">{{ number_format($item->total_bonus ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <!-- Jika tidak ada data, tampilkan baris kosong / pesan bahwa data tidak ditemukan -->
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 text-sm">
                                    <i class="fas fa-info-circle mb-2 text-lg"></i>
                                    <p>Tidak ada data bonus yang ditemukan pada periode atau pencarian ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Tabel (Pagination & Export) -->
                <div class="flex flex-col md:flex-row items-center justify-between px-4 py-3 bg-white border-t border-gray-200 gap-3">
                    <div class="text-sm text-gray-500">
                        @if(isset($bonuses) && method_exists($bonuses, 'total') && $bonuses->total() > 0)
                            Menampilkan {{ $bonuses->firstItem() }}-{{ $bonuses->lastItem() }} dari {{ $bonuses->total() }} data
                        @else
                            Menampilkan {{ count($bonuses ?? []) }} data
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Pagination Bawaan Laravel (Otomatis Aktif 1-5, Next, Prev) -->
                        @if(isset($bonuses) && method_exists($bonuses, 'links'))
                            <div>
                                {{ $bonuses->appends(request()->query())->links() }}
                            </div>
                        @endif

                                                <!-- Tombol Export ke Excel -->
                        <a href="/bonus/export?bulan={{ request('bulan', date('m')) }}" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-4 py-2 rounded flex items-center space-x-1.5 transition">
    <i class="fas fa-file-excel"></i> <span>Export ke Excel</span>
</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL PENGATURAN BONUS                     -->
    <!-- ========================================== -->
    <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4" x-cloak style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all" @click.away="openModal = false">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Pengaturan Bonus</h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form id="formPengaturanBonus">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="minimal_views" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jumlah Views untuk Mendapat Bonus</label>
                        <input type="number" id="minimal_views" name="minimal_views" value="{{ $pengaturan->minimal_views ?? 3000 }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-gray-500" required>
                        <p class="text-xs text-gray-400 mt-1">Masukkan ambang batas penayangan untuk memicu bonus.</p>
                    </div>

                    <div>
                        <label for="nominal_bonus" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Besar Bonus</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">Rp</span>
                            <input type="number" step="0.01" id="nominal_bonus" name="nominal_bonus" value="{{ $pengaturan->nominal_bonus ?? 50000 }}" class="w-full px-3 py-2 border border-gray-300 rounded-r-md text-sm focus:outline-none focus:border-gray-500" required>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Nominal bonus yang diberikan per kelipatan views di atas.</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <button @click="openModal = false" type="button" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-100 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-xs font-medium hover:bg-red-700 transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Memanggil Komponen Footer -->
    @include('components.footer')

    <!-- Script untuk Filter Bulan & Search Realtime / Enter -->
    <script>
        // 1. Filter Bulan Berfungsi (Otomatis reload halaman membawa parameter bulan)
        document.getElementById('filter-bulan').addEventListener('change', function() {
            let selectedMonth = this.value;
            let url = new URL(window.location.href);
            url.searchParams.set('bulan', selectedMonth);
            window.location.href = url.toString();
        });

        // 2. Fitur Search Berfungsi (Mencari saat menekan tombol Enter)
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                let keyword = this.value;
                let url = new URL(window.location.href);
                if (keyword) {
                    url.searchParams.set('search', keyword);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }
        });

        // 3. Script Submit Pengaturan Bonus
        document.getElementById('formPengaturanBonus').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = {
                minimal_views: document.getElementById('minimal_views').value,
                nominal_bonus: document.getElementById('nominal_bonus').value,
                _token: '{{ csrf_token() }}'
            };

            fetch('/api/v1/admin/pengaturan-bonus', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success' || data.message || response.ok) {
                    alert('Pengaturan bonus berhasil diperbarui');
                    location.reload();
                } else {
                    alert('Gagal memperbarui pengaturan.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data.');
            });
        }); 
    </script>
</body>
</html>