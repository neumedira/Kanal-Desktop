<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\PengaturanBonus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class BonusController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
{
    $bulan = (int) $request->query('bulan', date('m'));
    // Untuk pencarian database ('like'), kita tidak perlu strtolower karena MySQL umumnya sudah case-insensitive
    $search = $request->query('search');

    // ==========================================
    // 1. DATA PENGATURAN
    // ==========================================
    $pengaturanBonus = PengaturanBonus::first();

    // ==========================================
    // 2. QUERY DATA BONUS DENGAN PENCARIAN & PAGINASI
    // ==========================================
    $bonuses = Bonus::with(['wartawan', 'artikel'])
        ->where('periode_bulan', $bulan)
        ->when($search, function ($query) use ($search) {
            // Pencarian di relasi wartawan atau artikel
            $query->where(function ($q) use ($search) {
                $q->whereHas('wartawan', function ($qWartawan) use ($search) {
                    $qWartawan->where('nama', 'like', '%' . $search . '%');
                })
                ->orWhereHas('artikel', function ($qArtikel) use ($search) {
                    $qArtikel->where('judul', 'like', '%' . $search . '%');
                });
            });
        })
        ->paginate(3);

    // Menyisipkan parameter ke URL pagination agar saat pindah halaman, filter tidak hilang
    $bonuses->appends([
        'bulan'  => $bulan,
        'search' => $search
    ]);

    // ==========================================
    // 3. RETURN KE VIEW / API
    // ==========================================
    if (!$request->wantsJson()) {
        return view('bonus', [
            'bonuses'    => $bonuses, // Gunakan variabel $bonuses yang sudah dipaginate
            'pengaturan' => $pengaturanBonus
        ]);
    }

    return $this->successResponse($bonuses, 'Data bonus berhasil dimuat');
}

// --- FUNGSI EXPORT EXCEL BONUS ---
    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->query('bulan', date('m'));
        $namaBulan = \Carbon\Carbon::createFromDate(date('Y'), $bulan, 1)->translatedFormat('F');

        $fileName = 'Rekap_Bonus_' . $namaBulan . '_' . date('Y') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BonusExport($bulan), $fileName);
    }
    
    // Helper untuk membuat struktur dummy
    private function createDummyItem($nama, $judul, $link, $views, $bonus)
    {
        $item = new stdClass();
        $item->wartawan = (object) ['nama' => $nama];
        $item->artikel = (object) ['judul' => $judul, 'link' => $link];
        $item->views_saat_dihitung = $views;
        $item->total_bonus = $bonus;
        return $item;
    }
}