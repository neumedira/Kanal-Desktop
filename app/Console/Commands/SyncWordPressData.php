<?php

namespace App\Console\Commands;

use App\Services\WordPressService;
use Illuminate\Console\Command;

class SyncWordPressData extends Command
{
    /**
     * Perintah yang diketik di terminal
     */
    protected $signature = 'sync:wordpress {--pages=1 : Jumlah halaman artikel yang ingin ditarik}';

    /**
     * Deskripsi singkat perintah
     */
    protected $description = 'Sinkronisasi Kategori, Wartawan, dan Artikel dari API WordPress Kanal Kalimantan';

    /**
     * Jalankan proses sinkronisasi
     */
    public function handle(WordPressService $wpService): void
    {
        $this->info('Mulai sinkronisasi data dari WordPress...');

        // 1. Sync Kategori
        $this->line('1. Menarik data Kategori...');
        $wpService->syncKategori();
        $this->info('✓ Kategori berhasil disimpan.');

        // 2. Sync Wartawan
        $this->line('2. Menarik data Wartawan/Author...');
        $wpService->syncWartawan();
        $this->info('✓ Data Wartawan berhasil disimpan.');

        // 3. Sync Artikel
        $pages = (int) $this->option('pages');
        $this->line("3. Menarik data Artikel ({$pages} halaman)...");

        for ($page = 1; $page <= $pages; $page++) {
            $this->line("   - Mengambil halaman {$page}...");
            $wpService->syncArtikel($page, 20);
        }

        $this->info('✓ Data Artikel berhasil disimpan.');
        $this->info('Proses sinkronisasi selesai 100%!');
    }
}
