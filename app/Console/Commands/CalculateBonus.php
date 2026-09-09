<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Artikel;
use App\Models\PengaturanBonus;
use App\Models\Bonus;
use Carbon\Carbon;

class CalculateBonus extends Command
{
    protected $signature = 'bonus:calculate';
    protected $description = 'Menghitung dan mencatat bonus wartawan secara otomatis';

    public function handle()
    {
        $pengaturan = PengaturanBonus::first();
        if (!$pengaturan) {
            $this->error('Pengaturan bonus belum diatur.');
            return;
        }

        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Cari artikel bulan ini yang views-nya memenuhi syarat
        $artikelLolos = Artikel::whereMonth('tanggal_terbit', $bulanIni)
            ->whereYear('tanggal_terbit', $tahunIni)
            ->where('total_views', '>=', $pengaturan->minimal_views)
            ->get();

        $count = 0;
        foreach ($artikelLolos as $artikel) {
            Bonus::updateOrCreate(
                [
                    'artikel_id'    => $artikel->id,
                    'periode_bulan' => $bulanIni,
                    'periode_tahun' => $tahunIni,
                ],
                [
                    'wartawan_id'            => $artikel->wartawan_id,
                    'views_saat_dihitung'    => $artikel->total_views,
                    'minimal_views_saat_itu' => $pengaturan->minimal_views,
                    'nominal_bonus_saat_itu' => $pengaturan->nominal_bonus,
                    'total_bonus'            => $pengaturan->nominal_bonus,
                    'sumber'                 => 'otomatis',
                    'ditambahkan_oleh'       => null,
                ]
            );
            $count++;
        }

        $this->info("Kalkulasi selesai. {$count} artikel mendapatkan bonus.");
    }
}
