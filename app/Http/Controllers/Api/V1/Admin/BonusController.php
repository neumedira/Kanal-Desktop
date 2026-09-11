<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\PengaturanBonus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $bulan = (int) $request->query('bulan', date('m'));
        $tahun = (int) $request->query('tahun', date('Y'));
        $search = $request->query('search'); // Menangkap keyword pencarian

        // Query data bonus dengan relasi artikel & wartawan
        $query = Bonus::with(['artikel:id,judul,link,tanggal_terbit', 'wartawan:id,nama'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun);

        // Jika user mengetik sesuatu di kolom pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                // Cari berdasarkan nama wartawan ATAU judul artikel
                $q->whereHas('wartawan', function($w) use ($search) {
                    $w->where('nama', 'like', "%{$search}%");
                })->orWhereHas('artikel', function($a) use ($search) {
                    $a->where('judul', 'like', "%{$search}%");
                });
            });
        }

        // Gunakan paginate(5) agar pagination dinamis (sesuai tampilan 5 data per halaman)
        $bonuses = $query->paginate(5)->appends($request->query());

        // Ambil pengaturan bonus untuk modal
        $pengaturan = PengaturanBonus::first();

        // Jika request dari browser (bukan API JSON)
        if (!$request->wantsJson()) {
            return view('bonus', [
                'bonuses' => $bonuses,
                'pengaturan' => $pengaturan
            ]);
        }

        return $this->successResponse($bonuses, 'Data bonus berhasil dimuat');
    }
}