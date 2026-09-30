<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bonus;
use App\Models\PengaturanBonus;

class BonusController extends Controller
{
    // Menampilkan halaman bonus
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $search = $request->input('search');

        // Ambil pengaturan bonus aktif
        $pengaturan = PengaturanBonus::first() ?? (object) [
            'minimal_views' => 3000,
            'nominal_bonus' => 50000
        ];

        // Query data bonus berdasarkan periode bulan dan relasi
        $query = Bonus::with(['wartawan', 'artikel'])
                      ->where('periode_bulan', $bulan);

        // Fitur pencarian berdasarkan judul artikel atau nama wartawan
        if ($search) {
            $query->whereHas('artikel', function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%");
            })->orWhereHas('wartawan', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        // Pagination 10 data per halaman
        $bonuses = $query->paginate(10);

        return view('bonus', compact('bonuses', 'pengaturan'));
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