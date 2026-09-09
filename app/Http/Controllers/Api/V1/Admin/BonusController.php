<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $bulan = (int) $request->query('bulan', date('m'));
        $tahun = (int) $request->query('tahun', date('Y'));

        $bonus = Bonus::with(['artikel:id,judul,link,tanggal_terbit', 'wartawan:id,nama'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        return $this->successResponse($bonus, 'Data bonus berhasil dimuat');
    }
}
