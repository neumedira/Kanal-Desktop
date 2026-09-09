<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $bulan = (int) $request->query('bulan', date('m'));
        $tahun = (int) $request->query('tahun', date('Y'));

        $query = Artikel::whereMonth('tanggal_terbit', $bulan)
                        ->whereYear('tanggal_terbit', $tahun);

        $data = [
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'total_berita'  => $query->count(),
            'total_views'   => $query->sum('total_views'),
        ];

        return $this->successResponse($data, 'Statistik berhasil dimuat');
    }
}
