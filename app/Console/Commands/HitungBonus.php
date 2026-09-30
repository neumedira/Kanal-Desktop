<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Artikel;
use App\Models\Bonus;
use App\Models\PengaturanBonus;
use Carbon\Carbon;

class HitungBonus extends Command
{
    // Nama perintah di terminal
    protected $signature = 'bonus:hitung';
    
    // Deskripsi perintah
    protected $description = 'Scraping views dari URL web dan merekapitulasi bonus pewarta';

    public function handle()
    {
        $this->info("Memulai proses scraping views dan perhitungan bonus...");

        // Ambil pengaturan bonus (fallback jika belum ada di database)
        $pengaturan = PengaturanBonus::first();
        $minimalViews = $pengaturan->minimal_views ?? 3000;
        $nominalBonus = $pengaturan->nominal_bonus ?? 50000;

        // Hanya ambil artikel yang belum pernah disinkronisasi sama sekali
$artikels = Artikel::whereNull('last_synced_at')->get();
        $totalDihitung = 0;

        if ($artikels->isEmpty()) {
            $this->warn("Tidak ada artikel yang perlu dihitung atau semua sudah memiliki views.");
            return;
        }

        // Progress bar agar Anda bisa memantau proses berjalannya di terminal
        $bar = $this->output->createProgressBar(count($artikels));
        $bar->start();

        foreach ($artikels as $artikel) {
            $views = 0;
            $url = $artikel->link; 

            try {
                // Proses Scraping: Buka URL dengan batas waktu 10 detik agar terminal tidak hang
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    
                    // Regex untuk mencari kata "Dilihat: 1.500" atau "Dilihat: 1500"
                    if (preg_match('/<span class="post-views-count">([\d\.,]+)<\/span>/i', $html, $matches)) {
                        // Hilangkan titik atau koma jika ada (misal 1.500 menjadi angka 1500)
                        $views = (int) str_replace(['.', ','], '', $matches[1]);
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika URL gagal dimuat (misal server down atau timeout), lanjut ke artikel berikutnya
            }

            // Simpan hasil views ke database kolom total_views
            $artikel->total_views = $views;
$artikel->last_synced_at = Carbon::now(); // Tandai waktu prosesnya agar tidak diulang besok
$artikel->save();

            // Logika Hitung Bonus
            if ($views >= $minimalViews) {
                $tanggalTerbit = Carbon::parse($artikel->tanggal_terbit);
                $kelipatan = floor($views / $minimalViews);
                $totalBonus = $kelipatan * $nominalBonus;

                Bonus::updateOrCreate(
                    ['artikel_id' => $artikel->id], // Mencegah duplikasi bonus untuk artikel yang sama
                    [
                        'wartawan_id'            => $artikel->wartawan_id,
                        'periode_bulan'          => $tanggalTerbit->format('n'),
                        'periode_tahun'          => $tanggalTerbit->format('Y'),
                        'views_saat_dihitung'    => $views,
                        'minimal_views_saat_itu' => $minimalViews,
                        'nominal_bonus_saat_itu' => $nominalBonus,
                        'total_bonus'            => $totalBonus,
                        'sumber'                 => 'otomatis_sistem',
                    ]
                );
                $totalDihitung++;
            }

            // Memberikan jeda 0.5 detik di setiap perulangan agar server Kanal Kalimantan tidak down karena di-spam request
            usleep(500000); 
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! Berhasil menemukan {$totalDihitung} artikel yang memenuhi syarat bonus.");
    }
}