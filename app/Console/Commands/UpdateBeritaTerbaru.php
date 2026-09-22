<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Artikel;
use App\Models\KategoriBerita;
use App\Models\Wartawan;

class UpdateBeritaTerbaru extends Command
{
    protected $signature = 'update:berita';
    protected $description = 'Mengambil 100 berita terbaru untuk update harian';

    public function handle()
    {
        $this->info("Mengecek berita terbaru dari Kanal Kalimantan...");

        // Hanya ambil halaman 1 (100 berita terbaru)
        $postsResponse = Http::get('https://kanalkalimantan.com/wp-json/wp/v2/posts', [
            'per_page' => 100,
            'page' => 1,
            '_embed' => true
        ]);

        if ($postsResponse->successful() && !empty($postsResponse->json())) {
            $posts = $postsResponse->json();
            $countBaru = 0;

            foreach ($posts as $post) {
                // 1. Tangani Penulis
                $authorName = $post['_embedded']['author'][0]['name'] ?? 'Redaksi Kanal';
                $wartawan = Wartawan::firstOrCreate(['nama' => $authorName]);

                // 2. Tangani Kategori
                $kategoriId = null;
                if (isset($post['_embedded']['wp:term'][0][0])) {
                    $wpCat = $post['_embedded']['wp:term'][0][0];
                    $katModel = KategoriBerita::firstOrCreate(
                        ['nama_kategori' => html_entity_decode($wpCat['name'])],
                        ['slug' => $wpCat['slug']]
                    );
                    $kategoriId = $katModel->id;
                }

                if (!$kategoriId) {
                    $katModel = KategoriBerita::firstOrCreate(['nama_kategori' => 'Umum', 'slug' => 'umum']);
                    $kategoriId = $katModel->id;
                }

                // 3. Simpan Artikel (Gunakan wasRecentlyCreated untuk mengecek apakah ini berita baru)
                $artikel = Artikel::updateOrCreate(
                    ['link' => $post['link']],
                    [
                        'wp_post_id'     => $post['id'],
                        'judul'          => html_entity_decode($post['title']['rendered']),
                        'kategori_id'    => $kategoriId,
                        'wartawan_id'    => $wartawan->id,
                        'tanggal_terbit' => date('Y-m-d H:i:s', strtotime($post['date'])),
                        'keterangan'     => 'Update Otomatis',
                    ]
                );

                if ($artikel->wasRecentlyCreated) {
                    $countBaru++;
                }
            }
            $this->info("Selesai! Berhasil menambahkan {$countBaru} berita baru.");
        } else {
            $this->error("Gagal terhubung ke API WordPress.");
        }
    }
}