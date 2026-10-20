<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bonus;
use App\Models\PengaturanBonus;
use App\Exports\BonusExport;
use Maatwebsite\Excel\Facades\Excel;

class BonusController extends Controller
{
    // Menampilkan halaman bonus
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $search = $request->input('search');

        // Daftar tahun untuk dropdown: semua tahun yang punya data bonus + tahun ini
        $daftarTahun = Bonus::query()->distinct()->pluck('periode_tahun')
            ->push((int) date('Y'))->unique()->sortDesc()->values();

        // Ambil pengaturan bonus aktif
        $pengaturan = PengaturanBonus::first() ?? (object) [
            'minimal_views' => 3000,
            'nominal_bonus' => 50000
        ];

        // Query data bonus berdasarkan periode bulan dan relasi
        $query = Bonus::with(['wartawan', 'artikel'])
                      ->where('periode_bulan', $bulan)
                      ->where('periode_tahun', $tahun);

        // Fitur pencarian berdasarkan judul artikel atau nama wartawan.
        // Dibungkus satu grup supaya filter bulan & tahun tidak ikut terlewati oleh OR.
        if ($search) {
            $query->where(function ($outer) use ($search) {
                $outer->whereHas('artikel', function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%");
                })->orWhereHas('wartawan', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                });
            });
        }

        // Pagination 10 data per halaman
        $bonuses = $query->paginate(10)->withQueryString();

        return view('bonus', compact('bonuses', 'pengaturan', 'tahun', 'daftarTahun'));
    }

    // Unduh laporan bonus per bulan & tahun sebagai file Excel
    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));

        if ($bulan < 1 || $bulan > 12) {
            abort(422, 'Bulan tidak valid');
        }

        return Excel::download(new BonusExport($bulan, $tahun), "Bonus_Berita_{$bulan}_{$tahun}.xlsx");
    }

    // Menyimpan pengaturan dari Modal Alpine.js via Fetch API
    public function updatePengaturan(Request $request)
    {
        $request->validate([
            'minimal_views' => 'required|numeric',
            'nominal_bonus' => 'required|numeric',
        ]);

        $pengaturan = PengaturanBonus::first();
        
        if (!$pengaturan) {
            $pengaturan = new PengaturanBonus();
        }

        $pengaturan->minimal_views = $request->minimal_views;
        $pengaturan->nominal_bonus = $request->nominal_bonus;
        
        if (auth()->check()) {
            $pengaturan->updated_by = auth()->id();
        }
        
        $pengaturan->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Pengaturan bonus berhasil diperbarui'
        ]);
    }
}