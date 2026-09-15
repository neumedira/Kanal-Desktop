<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KategoriBerita;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Exports\KategoriExport;
use Maatwebsite\Excel\Facades\Excel;

class KategoriController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $query = KategoriBerita::withCount('artikel');

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategori = $query->get();

        return $this->successResponse($kategori, 'Berhasil mengambil data kategori');
    }

    public function show($id)
    {
        $kategori = KategoriBerita::withCount('artikel')->find($id);

        if (!$kategori) {
            return $this->errorResponse('Kategori tidak ditemukan', 404);
        }

        return $this->successResponse($kategori, 'Berhasil mengambil detail kategori');
    }

  // --- FUNGSI EXPORT EXCEL KATEGORI ---
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids) {
            return $this->errorResponse('Tidak ada data kategori yang dipilih', 400);
        }

        $fileName = 'Export_Kategori_' . date('Y-m-d') . '.xlsx';
        
        // Memanggil class KategoriExport yang baru saja kita buat
        return Excel::download(new KategoriExport($ids), $fileName);
    }
}