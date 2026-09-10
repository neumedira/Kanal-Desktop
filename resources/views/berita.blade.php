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
                    <select id="kategoriFilter" onchange="handleStateChange()" 
                        class="w-full py-2 px-3 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition bg-white">
                        <option value="">Semua Kategori</option>
                    </select>
                </div>

                <!-- Tanggal Dropdown (Hidden by default, muncul saat kategori dipilih) -->
                <div class="w-full md:w-56 hidden" id="tanggalFilterContainer">
    <div class="relative w-full">
        <input type="date" id="tanggalFilter" onchange="handleStateChange()"
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
                <div class="pl-10">Kategori</div>
                <div>Jumlah Berita</div>
            </div>

            <!-- Header Hasil Pencarian Artikel (Tampil saat kategori dipilih) -->
            <div id="artikelHeader" class="hidden bg-gray-50 p-4 border-b border-gray-200 text-sm font-bold text-gray-800">
                Hasil Pencarian: <span id="labelKategoriAktif"></span>
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

                <button class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-2 rounded transition flex items-center gap-2 shadow-sm">
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
        let allKategoriData = []; // Menyimpan data kategori untuk JS Pagination

        document.addEventListener('DOMContentLoaded', () => {
            initData();
        });

        async function initData() {
            await loadKategoriOptions();
            handleStateChange(); // Tentukan tampilan awal
        }

        // 1. Ambil Data Kategori & Isi Dropdown
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

        // 2. State Manager: Tentukan Tampilan Kategori atau Artikel
        function handleStateChange(page = 1) {
            currentPage = page;
            const kategoriId = document.getElementById('kategoriFilter').value;
            const tanggalContainer = document.getElementById('tanggalFilterContainer');
            const kategoriHeader = document.getElementById('kategoriHeader');
            const artikelHeader = document.getElementById('artikelHeader');

            if (kategoriId === "") {
                // STATE 1: Tampilkan List Kategori
                tanggalContainer.classList.add('hidden');
                artikelHeader.classList.add('hidden');
                kategoriHeader.classList.remove('hidden');
                renderKategoriList();
            } else {
                // STATE 2: Tampilkan List Artikel
                tanggalContainer.classList.remove('hidden');
                kategoriHeader.classList.add('hidden');
                artikelHeader.classList.remove('hidden');
                
                const select = document.getElementById('kategoriFilter');
                document.getElementById('labelKategoriAktif').textContent = select.options[select.selectedIndex].text;
                
                fetchArtikelList();
            }
        }

        // 3. Render State 1: Tabel Kategori (Client-side Pagination)
        function renderKategoriList() {
            const container = document.getElementById('dataListContainer');
            const searchVal = document.getElementById('searchInput').value.toLowerCase();
            
            let filteredData = allKategoriData;
            if (searchVal) {
                filteredData = allKategoriData.filter(c => (c.nama_kategori || c.nama).toLowerCase().includes(searchVal));
            }

            // Simple Pagination (5 per page)
            const perPage = 5;
            const total = filteredData.length;
            const lastPage = Math.ceil(total / perPage);
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const paginatedData = filteredData.slice(start, end);

            if (paginatedData.length === 0) {
                container.innerHTML = `<div class="p-8 text-center text-gray-500">Kategori tidak ditemukan.</div>`;
                renderPagination({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }, 'kategori');
                return;
            }

            container.innerHTML = paginatedData.map(item => `
                <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center gap-4">
                        <input type="checkbox" class="w-4 h-4 text-red-600 rounded border-gray-300">
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
        }

        // 4. Render State 2: Fetch & Tampilkan Artikel
        async function fetchArtikelList() {
            const container = document.getElementById('dataListContainer');
            const searchVal = document.getElementById('searchInput').value;
            const kategoriId = document.getElementById('kategoriFilter').value;
            const tanggalVal = document.getElementById('tanggalFilter').value;

            container.innerHTML = `<div class="p-8 text-center text-gray-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Memuat data...</div>`;

            let queryParams = new URLSearchParams({ page: currentPage, per_page: 5, kategori_id: kategoriId });
            if (searchVal) queryParams.append('search', searchVal);
            if (tanggalVal) queryParams.append('tanggal', tanggalVal); // Parameter opsional jika backend support

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
                                    <div class="space-y-3 w-full pr-4">
                                        <!-- Badge -->
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-50 rounded border border-blue-200">
                                            ${namaKategori}
                                        </span>
                                        
                                        <!-- Judul & Link -->
                                        <div>
                                            <h2 class="text-base font-bold text-gray-900 leading-snug">${item.judul}</h2>
                                            <a href="${item.link}" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1">
                                                <i class="fa-solid fa-link text-[10px]"></i> ${item.link}
                                            </a>
                                        </div>

                                        <!-- Meta Bottom -->
                                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2">
                                            <span class="flex items-center gap-1.5"><i class="fa-regular fa-user"></i> ${namaWartawan}</span>
                                            <span class="flex items-center gap-1.5 lg:hidden"><i class="fa-regular fa-calendar"></i> ${tgl}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Area: Checkbox & Date Desktop -->
                                    <div class="flex flex-col items-end justify-between h-full space-y-12">
                                        <input type="checkbox" class="w-5 h-5 text-red-600 rounded border-gray-300">
                                        <span class="hidden lg:flex items-center gap-1.5 text-xs text-gray-500 whitespace-nowrap">
                                            <i class="fa-regular fa-calendar"></i> ${tgl}
                                        </span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                    renderPagination(paginatedData, 'artikel');
                }
            } catch (error) {
                container.innerHTML = `<div class="p-8 text-center text-red-500">Terjadi kesalahan koneksi server.</div>`;
            }
        }

        // 5. Render Navigator Pagination
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
            searchTimeout = setTimeout(() => { handleStateChange(1); }, 500);
        }
    </script>
</body>
</html>