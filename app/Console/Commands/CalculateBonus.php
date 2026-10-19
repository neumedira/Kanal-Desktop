<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Artikel;
use App\Models\PengaturanBonus;
use App\Models\Bonus;
use Carbon\Carbon;

class CalculateBonus extends Command
{
    protected $signature = 'bonus:calculate
        {--bulan= : Bulan terbit artikel (1-12), default bulan ini}
        {--tahun= : Tahun terbit artikel, default tahun ini}';
    protected $description = 'Menghitung dan mencatat bonus wartawan secara otomatis';

    public function handle()
    {
        // Jika pengaturan belum ada, pakai nilai default yang sama dengan halaman bonus
        $pengaturan = PengaturanBonus::first() ?? (object) [
            'minimal_views' => 3000,
            'nominal_bonus' => 50000,
        ];

        $bulan = (int) ($this->option('bulan') ?: Carbon::now()->month);
        $tahun = (int) ($this->option('tahun') ?: Carbon::now()->year);

        // Cari artikel di periode tersebut yang views-nya memenuhi syarat
        $artikelLolos = Artikel::whereMonth('tanggal_terbit', $bulan)
            ->whereYear('tanggal_terbit', $tahun)
            ->where('total_views', '>=', $pengaturan->minimal_views)
            ->get();

        $count = 0;
        foreach ($artikelLolos as $artikel) {
            Bonus::updateOrCreate(
                [
                    'artikel_id'    => $artikel->id,
                    'periode_bulan' => $bulan,
                    'periode_tahun' => $tahun,
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

        $this->info("Kalkulasi selesai ({$bulan}/{$tahun}). {$count} artikel mendapatkan bonus.");
    }
}
