<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

use stdClass;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        // 1. DUMMY DATA: Pengaturan Bonus
        $pengaturan = new stdClass();
        $pengaturan->minimal_views = 3000;
        $pengaturan->nominal_bonus = 50000;

        // 2. DUMMY DATA: List Bonus (Membuat array of objects)
        $dummyDataList = collect([
            $this->createDummyItem('Ahmad Jaelani', 'Banjir Melanda Kawasan Banjarbaru, Warga Dievakuasi', 'https://kanalkalimantan.com/banjir-banjarbaru', 12500, 150000),
            $this->createDummyItem('Siti Aminah', 'Harga Kebutuhan Pokok Menjelang Lebaran Terpantau Stabil', 'https://kanalkalimantan.com/harga-pokok-stabil', 8200, 100000),
            $this->createDummyItem('Budi Santoso', 'Pemerintah Kota Resmikan Taman Baru di Pusat Kota', 'https://kanalkalimantan.com/peresmian-taman-baru', 4500, 50000),
            $this->createDummyItem('Rina Melati', 'Kecelakaan Lalu Lintas di Jalan Ahmad Yani, Arus Macet Panjang', 'https://kanalkalimantan.com/laka-lantas-ayani', 15300, 200000),
            $this->createDummyItem('Agus Setiawan', 'Festival Budaya Banjar Meriahkan Hari Jadi Kota', 'https://kanalkalimantan.com/festival-budaya-banjar', 2500, 0), // Belum mencapai minimal views
        ]);

        // 3. FITUR: Pencarian (Sederhana menggunakan collection filter)
        if ($request->has('search') && $request->search != '') {
            $searchKeyword = strtolower($request->search);
            $dummyDataList = $dummyDataList->filter(function ($item) use ($searchKeyword) {
                return str_contains(strtolower($item->wartawan->nama), $searchKeyword) || 
                       str_contains(strtolower($item->artikel->judul), $searchKeyword);
            });
        }

        // 4. FITUR: Pagination Manual (Agar fungsi links(), firstItem(), dll di Blade berfungsi)
        $perPage = 10;
        $page = $request->input('page', 1);
        $paginatedData = new LengthAwarePaginator(
            $dummyDataList->forPage($page, $perPage),
            $dummyDataList->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()] // Membawa query string seperti search & bulan
        );

        // 5. Kembalikan ke View
        // Pastikan nama view ('bonus.index') disesuaikan dengan struktur folder view Anda
        return view('bonus.index', [
            'bonuses' => $paginatedData,
            'pengaturan' => $pengaturan
        ]);
    }

    /**
     * Helper function untuk membuat object dummy dengan struktur yang sesuai Blade
     */
    private function createDummyItem($namaWartawan, $judulArtikel, $linkArtikel, $views, $bonus)
    {
        $item = new stdClass();

        // Relasi dummy Wartawan
        $item->wartawan = new stdClass();
        $item->wartawan->nama = $namaWartawan;

        // Relasi dummy Artikel
        $item->artikel = new stdClass();
        $item->artikel->judul = $judulArtikel;
        $item->artikel->link = $linkArtikel;

        // Atribut utama
        $item->views_saat_dihitung = $views;
        $item->total_bonus = $bonus;

        return $item;
    }
}