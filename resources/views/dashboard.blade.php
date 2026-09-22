<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kanal Kalimantan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col justify-between">
    <div class="w-full">
        @include('components.navbar')
        
        <div class="w-full px-6 md:px-12 py-8">
            <!-- Header & Filter Bulan/Tahun -->
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                <h1 class="text-2xl font-bold text-gray-800">Statistik Bulan Ini</h1>
                <div class="flex gap-2">
                    <select id="filterBulan" onchange="loadDashboardData()" class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <select id="filterTahun" onchange="loadDashboardData()" class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
            </div>

            <!-- Card Total Berita -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6 w-full">
                <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase mb-2">TOTAL BERITA</p>
                <h2 id="totalBerita" class="text-4xl font-bold text-gray-900 mb-4">-</h2>
                <div class="border-t border-gray-100 pt-3 flex items-center text-xs text-gray-500 gap-2">
                    <i class="fa-regular fa-circle-check text-gray-400"></i>
                    <span>artikel terbit bulan ini</span>
                </div>
            </div>

            <!-- Card Total Views -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6 w-full">
                <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase mb-2">TOTAL VIEWS</p>
                <h2 id="totalViews" class="text-4xl font-bold text-gray-900 mb-4">-</h2>
                <div class="border-t border-gray-100 pt-3 flex items-center text-xs text-gray-500 gap-2">
                    <i class="fa-solid fa-chart-bar text-gray-400"></i>
                    <span>akumulasi seluruh kategori</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Sesuai Gambar -->
    <footer class="bg-gray-100 border-t border-gray-200 py-6 px-8 mt-12 w-full">
        <div class="w-full px-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-lg text-black tracking-tight">Kanal Kalimantan</span>
            </div>
            <div class="flex gap-6 text-sm text-gray-600">
                <a href="#" class="hover:text-red-600">Redaksi</a>
                <a href="#" class="hover:text-red-600">Pedoman Media Siber</a>
                <a href="#" class="hover:text-red-600">Kontak</a>
                <a href="#" class="hover:text-red-600">Karir</a>
            </div>
            <p class="text-xs text-gray-500">&copy; 2026 Kanal Kalimantan. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const token = localStorage.getItem('access_token');
        const user = JSON.parse(localStorage.getItem('user_data') || '{}');

        if (!token) {
            alert('Kamu belum login!');
            window.location.href = '/login';
        }

        const dateNow = new Date();
        document.getElementById('filterBulan').value = dateNow.getMonth() + 1;
        document.getElementById('filterTahun').value = dateNow.getFullYear();

        async function loadDashboardData() {
            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;

            try {
                const response = await fetch(`/api/v1/admin/dashboard?bulan=${bulan}&tahun=${tahun}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const result = await response.json();
                console.log("Response API Dashboard:", result);

                if (response.ok && result.data) {
                    // Total Berita
                    document.getElementById('totalBerita').innerText = Number(result.data.total_berita || 0).toLocaleString('id-ID');
                    
                    // Total Views (Diprioritaskan menangkap 'total_view' sesuai respon console API backend)
                    const totalViewsValue = result.data.total_view ?? result.data.total_views ?? result.data.views ?? 0;
                    document.getElementById('totalViews').innerText = Number(totalViewsValue).toLocaleString('id-ID');
                } else {
                    if(response.status === 401) {
                        logout();
                    }
                }
            } catch (error) {
                console.error('Gagal memuat data statistik:', error);
            }
        }

        function logout() {
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_data');
            window.location.href = '/login';
        }

        loadDashboardData();
    </script>
</body>
</html>