<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshController extends Controller
{
    /**
     * Tombol "Refresh Data" di halaman Bonus.
     * Menjalankan tiga langkah yang sama dengan jadwal otomatis, khusus bulan berjalan:
     *   1. update:berita      -> ambil 100 berita terbaru dari WordPress
     *   2. views:sync         -> perbarui views artikel sejak tanggal 1 bulan ini
     *   3. bonus:calculate    -> hitung ulang bonus bulan ini
     */
    public function refresh(): JsonResponse
    {
        // Cegah dua refresh berjalan bersamaan (mis. tombol diklik dua kali / dua admin)
        $lock = Cache::lock('refresh-data', 300);

        if (! $lock->get()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Refresh sedang berjalan. Coba lagi beberapa saat lagi.',
            ], 409);
        }

        try {
            @set_time_limit(300);
            ignore_user_abort(true);

            $now   = now();
            $bulan = $now->month;
            $tahun = $now->year;
            // Mulai dari tanggal 1 bulan ini (hari ke-1 tetap mengambil 1 hari terakhir)
            $days  = max(1, $now->day - 1);

            $hasil = [];
            $gagal = false;

            foreach ([
                'berita' => ['update:berita', []],
                'views'  => ['views:sync', ['--days' => $days]],
                'bonus'  => ['bonus:calculate', ['--bulan' => $bulan, '--tahun' => $tahun]],
            ] as $kunci => [$perintah, $opsi]) {
                $kode = Artisan::call($perintah, $opsi);
                $hasil[$kunci] = $this->barisAkhir(Artisan::output());

                if ($kode !== 0) {
                    $gagal = true;
                    break; // jangan lanjut menghitung bonus dari data yang gagal diambil
                }
            }

            return response()->json([
                'status'  => $gagal ? 'error' : 'success',
                'message' => implode("\n", array_filter($hasil)),
                'details' => $hasil,
            ], $gagal ? 502 : 200);
        } catch (\Throwable $e) {
            Log::error('Refresh data gagal: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Refresh gagal: ' . $e->getMessage(),
            ], 500);
        } finally {
            $lock->release();
        }
    }

    /** Ambil baris hasil terakhir dari output command (baris ringkasan). */
    private function barisAkhir(string $output): string
    {
        $baris = array_values(array_filter(array_map('trim', explode("\n", $output))));

        return $baris ? end($baris) : '';
    }
}
