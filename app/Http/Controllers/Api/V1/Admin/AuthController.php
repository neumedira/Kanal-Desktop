<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengaturanBonus; // Pastikan Model PengaturanBonus sudah ada

class SettingBonusController extends Controller
{
    // GET /api/v1/pengaturan-bonus
    public function index()
    {
        $setting = PengaturanBonus::first();
        return response()->json([
            'status' => 'success',
            'data'   => $setting
        ]);
    }

    // PUT /api/v1/pengaturan-bonus
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bonus_per_artikel' => 'nullable|numeric',
            'bonus_per_views'   => 'nullable|numeric',
            'min_views'         => 'nullable|integer',
        ]);

        $setting = PengaturanBonus::first();

        if (!$setting) {
            $setting = PengaturanBonus::create($validated);
        } else {
            $setting->update($validated);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan bonus berhasil diperbarui',
            'data'    => $setting
        ]);
    }
}
