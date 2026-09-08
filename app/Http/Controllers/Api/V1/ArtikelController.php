<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $artikel = Artikel::with(['kategori', 'wartawan'])
            ->search($request->search)
            ->kategori($request->kategori_id)
            ->filterTanggal($request->start_date, $request->end_date)
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse($artikel, 'Berhasil mengambil daftar artikel');
    }

    public function show($id)
    {
        $artikel = Artikel::with(['kategori', 'wartawan'])->find($id);

        if (!$artikel) {
            return $this->errorResponse('Artikel tidak ditemukan', 404);
        }

        return $this->successResponse($artikel, 'Berhasil mengambil detail artikel');
    }

    public function updateKeterangan(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string'
        ]);

        $artikel = Artikel::find($id);

        if (!$artikel) {
            return $this->errorResponse('Artikel tidak ditemukan', 404);
        }

        $artikel->keterangan = $request->keterangan;
        $artikel->save();

        return $this->successResponse($artikel, 'Berhasil memperbarui keterangan artikel');
    }
}
