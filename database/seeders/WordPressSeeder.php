<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Wartawan;

class WordPressSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil/buat data default kategori & wartawan agar Foreign Key tidak error
        $kategori = Kategori::firstOrCreate(['nama_kategori' => 'Umum']);
        $wartawan = Wartawan::firstOrCreate(['nama' => 'Redaksi Kanal']);

        // 2. Tembak REST API WordPress (Mengambil 15 berita terbaru)
        $response = Http::get('https://kanalkalimantan.com/wp-json/wp/v2/posts?per_page=15');

        if ($response->successful()) {
            $posts = $response->json();
            $count = 0;

            foreach ($posts as $post) {
                // 3. Simpan atau perbarui data ke tabel artikels
                Artikel::updateOrCreate(
                    ['link' => $post['link']], // Acuan unik agar data tidak duplikat
                    [
                        'judul'          => html_entity_decode($post['title']['rendered']),
                        'kategori_id'    => $kategori->id,
                        'wartawan_id'    => $wartawan->id,
                        'tanggal_terbit' => date('Y-m-d H:i:s', strtotime($post['date'])),
                        'keterangan'     => 'Import dari WordPress',
                    ]
                );
                $count++;
            }

            $this->command->info("Berhasil mengimpor {$count} berita dari WordPress!");
        } else {
            $this->command->error('Gagal mengambil data dari REST API WordPress.');
        }
    }
}
