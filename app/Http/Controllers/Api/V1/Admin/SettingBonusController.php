<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengaturanBonus; // Pastikan model PengaturanBonus / SettingBonus kamu sudah ada

class SettingBonusController extends Controller
{
    // GET /api/v1/admin/pengaturan-bonus
    public function index()
    {
        $setting = PengaturanBonus::first();

        return response()->json([
            'status' => 'success',
            'data'   => $setting
        ]);
    }

    // PUT /api/v1/admin/pengaturan-bonus
    public function update(Request $request)
    {
        $validated = $request->validate([
            'minimal_views' => 'nullable|numeric',
            'nominal_bonus'   => 'nullable|numeric',
        ]);

        $setting = PengaturanBonus::first();

        if (!$setting) {
            $setting = PengaturanBonus::create($validated);
        } else {
            PengaturanBonus::where('id', $setting->id)->update([
                'minimal_views' => $request->minimal_views,
                'nominal_bonus' => $request->nominal_bonus,
                'updated_by' => auth()->user()->id,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan bonus berhasil diperbarui',
            'data'    => $setting
        ]);
    }
}
