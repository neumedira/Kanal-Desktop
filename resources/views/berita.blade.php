<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasifikasi Berita - Kanal Kalimantan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

    @include('components.navbar')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Klasifikasi Berita</h1>
            <p class="text-sm text-gray-500 mt-1">Beranda / Berita / <span class="text-gray-700 font-medium">Klasifikasi Berita</span></p>
        </div>

        <!-- Filter Area -->
        <div class="bg-white rounded-t-lg shadow-sm p-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <!-- Search -->
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="searchInput" oninput="debounceSearch()" placeholder="Cari Data..." 
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition">
                </div>

                <!-- Kategori Dropdown -->
                <div class="w-full md:w-56">
                    <select id="kategoriFilter" onchange="handleFilterChange()" 
                        class="w-full py-2 px-3 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                        <option value="">Semua Kategori</option>
                    </select>
                </div>

                <!-- Tanggal Dropdown (Hidden by default, muncul saat kategori dipilih) -->
                <div class="w-full md:w-56 hidden" id="tanggalFilterContainer">
                    <div class="relative w-full">
                        <input type="date" id="tanggalFilter" onchange="handleFilterChange()"
                            class="w-full py-2 px-3 text-sm text-gray-700 border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white"
                            title="Filter per tanggal">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Container -->
        <div class="bg-white rounded-b-lg shadow-sm border-x border-b border-gray-200 overflow-hidden">
            
            <!-- Header List Kategori (Tampil di Awal) -->
            <div id="kategoriHeader" class="flex justify-between items-center bg-gray-50 p-4 border-b border-gray-200 text-sm font-semibold text-gray-600">
                <!-- [BARU] Tambah checkbox Select All Kategori -->
                <div class="flex items-center gap-4 pl-4">
                    <input type="checkbox" id="selectAllKategori" onclick="toggleSelectAll(this)" class="w-4 h-4 text-red-600 rounded border-gray-300 cursor-pointer">
                    <span>Kategori</span>
                </div>
                <div>Jumlah Berita</div>
            </div>

            <!-- Header Hasil Pencarian Artikel (Tampil saat kategori dipilih) -->
            <!-- [BARU] Tambah flex layout dan checkbox Select All Artikel -->
            <div id="artikelHeader" class="hidden bg-gray-50 p-4 border-b border-gray-200 text-sm font-bold text-gray-800 flex justify-between items-center">
                <div class="flex items-center gap-4 pl-4">
                    <input type="checkbox" id="selectAllArtikel" onclick="toggleSelectAll(this)" class="w-4 h-4 text-red-600 rounded border-gray-300 cursor-pointer">
                    <span>Hasil Pencarian: <span id="labelKategoriAktif"></span></span>
                </div>
            </div>

            <!-- Render Data List -->
            <div id="dataListContainer" class="divide-y divide-gray-100">
                <!-- Data di-inject via JS -->
            </div>

            <!-- Footer Pagination -->
            <div class="p-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span id="paginationInfo" class="text-sm text-gray-500">Menampilkan 0 data</span>
                
                <div id="paginationNav" class="flex items-center gap-1">
                    <!-- Pagination Buttons JS -->
                </div>

                <!-- [BARU] Tambahkan id="btnExport" -->
                <button id="btnExport" class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-download"></i> Export ke Excel
                </button>
            </div>
        </div>
    </main>

    @include('components.footer')

    <script>
        const token = localStorage.getItem('access_token');
        if (!token) window.location.href = '/login';

        let searchTimeout = null;
        let currentPage = 1;
        let allKategoriData = []; 
        
        // [BARU] State Management untuk Checkbox dan Export
        let selectedIds = [];
        let currentMode = 'kategori'; // 'kategori' atau 'artikel'

        document.addEventListener('DOMContentLoaded', () => {
            initData();
            setupExportButton(); // [BARU] Inisialisasi tombol export
        });

        async function initData() {
            await loadKategoriOptions();
            handleStateChange(1); 
        }

        async function loadKategoriOptions() {
            try {
                const response = await fetch('/api/v1/kategori', {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (response.ok && result.data) {
                    allKategoriData = Array.isArray(result.data) ? result.data : (result.data.data || []);
                    
                    const select = document.getElementById('kategoriFilter');
                    select.innerHTML = '<option value="">Semua Kategori</option>';
                    
                    allKategoriData.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.nama_kategori || cat.nama;
                        select.appendChild(opt);
                    });
                }
            } catch (error) {
                console.error('Error load kategori:', error);
            }
        }

        // [BARU] Handler khusus saat dropdown filter berubah, agar state checklist di-reset
        function handleFilterChange() {
            selectedIds = []; // Reset pilihan saat ganti filter/kategori
            handleStateChange(1);
        }

        function handleStateChange(page = 1) {
            currentPage = page;
            const kategoriId = document.getElementById('kategoriFilter').value;
            const tanggalContainer = document.getElementById('tanggalFilterContainer');
            const kategoriHeader = document.getElementById('kategoriHeader');
            const artikelHeader = document.getElementById('artikelHeader');

            const newMode = (kategoriId === "") ? 'kategori' : 'artikel';
            
            // [BARU] Reset selected ID jika mode berubah dari Kategori ke Artikel atau sebaliknya
            if (currentMode !== newMode) {
                selectedIds = [];
                currentMode = newMode;
            }

            if (currentMode === 'kategori') {
                tanggalContainer.classList.add('hidden');
                artikelHeader.classList.add('hidden');
                artikelHeader.classList.remove('flex'); // [BARU] styling adjust
                kategoriHeader.classList.remove('hidden');
                kategoriHeader.classList.add('flex'); // [BARU] styling adjust
                renderKategoriList();
            } else {
                tanggalContainer.classList.remove('hidden');
                kategoriHeader.classList.add('hidden');
                kategoriHeader.classList.remove('flex'); // [BARU] styling adjust
                artikelHeader.classList.remove('hidden');
                artikelHeader.classList.add('flex'); // [BARU] styling adjust
                
                const select = document.getElementById('kategoriFilter');
                document.getElementById('labelKategoriAktif').textContent = select.options[select.selectedIndex].text;
                
                fetchArtikelList();
            }
        }

        function renderKategoriList() {
            const container = document.getElementById('dataListContainer');
            const searchVal = document.getElementById('searchInput').value.toLowerCase();
            
            let filteredData = allKategoriData;
            if (searchVal) {
                filteredData = allKategoriData.filter(c => (c.nama_kategori || c.nama).toLowerCase().includes(searchVal));
            }

            const perPage = 5;
            const total = filteredData.length;
            const lastPage = Math.ceil(total / perPage);
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const paginatedData = filteredData.slice(start, end);

            if (paginatedData.length === 0) {
                container.innerHTML = `<div class="p-8 text-center text-gray-500">Kategori tidak ditemukan.</div>`;
                renderPagination({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }, 'kategori');
                checkMasterStatus(); // [BARU]
                return;
            }

            container.innerHTML = paginatedData.map(item => `
                <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center gap-4 pl-4">
                        <!-- [BARU] Checkbox Item Kategori -->
                        <input type="checkbox" value="${item.id}" onclick="toggleSelection(${item.id})" 
                            class="item-checkbox w-4 h-4 text-red-600 rounded border-gray-300 cursor-pointer"
                            ${selectedIds.includes(item.id) ? 'checked' : ''}>
                        <span class="text-sm font-medium text-gray-800">${item.nama_kategori || item.nama}</span>
                    </div>
                    <div class="text-sm text-gray-500 mr-4">${item.artikel_count || 0}</div>
                </div>
            `).join('');

            renderPagination({ 
                current_page: currentPage, 
                last_page: lastPage, 
                total: total, 
                from: total ? start + 1 : 0, 
                to: end > total ? total : end 
            }, 'kategori');

            checkMasterStatus(); // [BARU] Perbarui status master checkbox
        }

        async function fetchArtikelList() {
            const container = document.getElementById('dataListContainer');
            const searchVal = document.getElementById('searchInput').value;
            const kategoriId = document.getElementById('kategoriFilter').value;
            const tanggalVal = document.getElementById('tanggalFilter').value;

            container.innerHTML = `<div class="p-8 text-center text-gray-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Memuat data...</div>`;

            let queryParams = new URLSearchParams({ page: currentPage, per_page: 5, kategori_id: kategoriId });
            if (searchVal) queryParams.append('search', searchVal);
            if (tanggalVal) queryParams.append('tanggal', tanggalVal); 

            try {
                const response = await fetch(`/api/v1/artikel?${queryParams.toString()}`, {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (response.ok) {
                    const paginatedData = result.data || {};
                    const items = paginatedData.data || [];
                    
                    if (items.length === 0) {
                        container.innerHTML = `<div class="p-8 text-center text-gray-500">Data artikel tidak ditemukan.</div>`;
                    } else {
                        container.innerHTML = items.map(item => {
                            const namaKategori = item.kategori ? item.kategori.nama_kategori : '-';
                            const namaWartawan = item.wartawan ? (item.wartawan.nama || item.wartawan.name) : '-';
                            const tgl = item.tanggal_terbit ? new Date(item.tanggal_terbit).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-';

                            return `
                                <div class="p-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-b-0 flex justify-between items-start">
                                    <div class="space-y-3 w-full pl-4 pr-4">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-50 rounded border border-blue-200">
                                            ${namaKategori}
                                        </span>
                                        <div>
                                            <h2 class="text-base font-bold text-gray-900 leading-snug">${item.judul}</h2>
                                            <a href="${item.link}" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1">
                                                <i class="fa-solid fa-link text-[10px]"></i> ${item.link}
                                            </a>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-user"></i> ${namaWartawan}</span>
                                            <span class="flex items-center gap-1.5 lg:hidden"><i class="fa-regular fa-calendar"></i> ${tgl}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-end justify-between h-full space-y-12">
                                        <!-- [BARU] Checkbox Item Artikel -->
                                        <input type="checkbox" value="${item.id}" onclick="toggleSelection(${item.id})" 
                                            class="item-checkbox w-5 h-5 text-red-600 rounded border-gray-300 cursor-pointer"
                                            ${selectedIds.includes(item.id) ? 'checked' : ''}>
                                            
                                        <span class="hidden lg:flex items-center gap-1.5 text-xs text-gray-500 whitespace-nowrap">
                                            <i class="fa-regular fa-calendar"></i> ${tgl}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                    renderPagination(paginatedData, 'artikel');
                    checkMasterStatus(); // [BARU] Perbarui status master checkbox
                }
            } catch (error) {
                container.innerHTML = `<div class="p-8 text-center text-red-500">Terjadi kesalahan koneksi server.</div>`;
            }
        }

        function renderPagination(meta, mode) {
            const nav = document.getElementById('paginationNav');
            const info = document.getElementById('paginationInfo');
            const typeLabel = mode === 'kategori' ? 'kategori' : 'hasil';

            if (!meta || !meta.total) {
                info.innerText = `Menampilkan 0 dari 0 ${typeLabel}`;
                nav.innerHTML = '';
                return;
            }

            info.innerText = `Menampilkan ${meta.from}-${meta.to} dari ${meta.total} ${typeLabel}`;

            let btns = '';
            const buildBtn = (p, label, active = false) => `<button onclick="handleStateChange(${p})" class="px-3 py-1.5 text-sm border ${active ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'} rounded mx-0.5 transition">${label}</button>`;

            if (meta.current_page > 1) btns += buildBtn(meta.current_page - 1, '<i class="fa-solid fa-angle-left"></i>');
            
            for (let i = 1; i <= meta.last_page; i++) {
                if (i === 1 || i === meta.last_page || (i >= meta.current_page - 1 && i <= meta.current_page + 1)) {
                    btns += buildBtn(i, i, i === meta.current_page);
                } else if (i === meta.current_page - 2 || i === meta.current_page + 2) {
                    btns += `<span class="px-2 text-gray-400">...</span>`;
                }
            }

            if (meta.current_page < meta.last_page) btns += buildBtn(meta.current_page + 1, '<i class="fa-solid fa-angle-right"></i>');
            nav.innerHTML = btns;
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { 
                selectedIds = []; // [BARU] Reset checklist jika user melakukan pencarian baru
                handleStateChange(1); 
            }, 500);
        }

        /* =========================================
           [BARU] LOGIKA CHECKBOX DAN EXPORT EXCEL
           ========================================= */

        // 1. Pilih / Hapus satu item
        function toggleSelection(id) {
            const index = selectedIds.indexOf(id);
            if (index === -1) {
                selectedIds.push(id);
            } else {
                selectedIds.splice(index, 1);
            }
            checkMasterStatus(); 
        }

        // 2. Pilih Semua (Select All) di halaman yang sedang aktif
        function toggleSelectAll(masterCheckbox) {
            const isChecked = masterCheckbox.checked;
            const checkboxes = document.querySelectorAll('.item-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
                const id = parseInt(cb.value);
                const index = selectedIds.indexOf(id);
                
                if (isChecked && index === -1) {
                    selectedIds.push(id);
                } else if (!isChecked && index !== -1) {
                    selectedIds.splice(index, 1);
                }
            });
        }

        // 3. Sinkronisasi status master checkbox berdasarkan item di halaman aktif
        function checkMasterStatus() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const masterKategori = document.getElementById('selectAllKategori');
            const masterArtikel = document.getElementById('selectAllArtikel');
            
            if (checkboxes.length === 0) {
                if(masterKategori) masterKategori.checked = false;
                if(masterArtikel) masterArtikel.checked = false;
                return;
            }
            
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            if (currentMode === 'kategori' && masterKategori) {
                masterKategori.checked = allChecked;
            } else if (currentMode === 'artikel' && masterArtikel) {
                masterArtikel.checked = allChecked;
            }
        }

        // 4. Setup Tombol Export
        function setupExportButton() {
            document.getElementById('btnExport').addEventListener('click', () => {
                if (selectedIds.length === 0) {
                    alert('Silakan pilih minimal satu data untuk diekspor!');
                    return;
                }

                if(confirm(`Yakin ingin mengekspor ${selectedIds.length} data ${currentMode} terpilih?`)) {
                    
                    // Bangun URL Endpoint. Sesuaikan `/api/v1/export-..` dengan routing backend Laravel Anda.
                    const queryParams = new URLSearchParams();
                    selectedIds.forEach(id => queryParams.append('ids[]', id));
                    
                    // Opsional: sertakan token jika route export membutuhkan token via URL parameter (karena window.open tidak bisa pasang Header Authorization)
                    queryParams.append('token', token); 
                    
                    const exportUrl = `/api/v1/export-${currentMode}?${queryParams.toString()}`;
                    
                    // Buka tab baru untuk mendownload file Excel
                    window.open(exportUrl, '_blank');
                }
            });
        }

    </script>
</body>
</html>