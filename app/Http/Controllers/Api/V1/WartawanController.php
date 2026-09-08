<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Wartawan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class WartawanController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $query = Wartawan::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $wartawan = $query->get();

        return $this->successResponse($wartawan, 'Berhasil mengambil data wartawan');
    }

    public function show($id)
    {
        $wartawan = Wartawan::find($id);

        if (!$wartawan) {
            return $this->errorResponse('Wartawan tidak ditemukan', 404);
        }

        return $this->successResponse($wartawan, 'Berhasil mengambil detail wartawan');
    }
}
