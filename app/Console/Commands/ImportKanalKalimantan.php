<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Artikel;
use App\Models\KategoriBerita;
use App\Models\Wartawan;

class ImportKanalKalimantan extends Command
{
    // Nama perintah yang akan dipanggil di terminal
    protected $signature = 'import:berita';

    // Deskripsi perintah
    protected $description = 'Mengambil seluruh berita dari API Kanal Kalimantan';

    public function handle()
    {
        $page = 1;
        $perPage = 100; // WP API maksimal per_page adalah 100
        $totalImported = 0;
        $hasNextPage = true;

        $this->info("Memulai proses penarikan data...");

        while ($hasNextPage) {
            $this->info("Mengambil data halaman {$page}...");

            $postsResponse = Http::get('https://kanalkalimantan.com/wp-json/wp/v2/posts', [
                'per_page' => $perPage,
                'page' => $page,
                '_embed' => true // Memastikan data penulis & kategori ikut terbawa
            ]);

            // Jika response sukses dan datanya tidak kosong
            if ($postsResponse->successful() && !empty($postsResponse->json())) {
                $posts = $postsResponse->json();

                foreach ($posts as $post) {
                    // 1. Ambil Nama Wartawan/Penulis Asli
                    $authorName = 'Redaksi Kanal';
                    if (isset($post['_embedded']['author'][0]['name'])) {
                        $authorName = $post['_embedded']['author'][0]['name'];
                    }
                    $wartawan = Wartawan::firstOrCreate(['nama' => $authorName]);

                    // 2. Ambil Kategori Asli
                    $kategoriId = null;
                    if (isset($post['_embedded']['wp:term'][0][0])) {
                        $wpCat = $post['_embedded']['wp:term'][0][0];
                        $katModel = KategoriBerita::firstOrCreate(
                            ['nama_kategori' => html_entity_decode($wpCat['name'])],
                            ['slug' => $wpCat['slug']]
                        );
                        $kategoriId = $katModel->id;
                    }

                    // Fallback Kategori
                    if (!$kategoriId) {
                        $katModel = KategoriBerita::firstOrCreate(
                            ['nama_kategori' => 'Umum'],
                            ['slug' => 'umum']
                        );
                        $kategoriId = $katModel->id;
                    }

                    // 3. Simpan Artikel
                    Artikel::updateOrCreate(
                        ['link' => $post['link']],
                        [
                            'wp_post_id'     => $post['id'],
                            'judul'          => html_entity_decode($post['title']['rendered']),
                            'kategori_id'    => $kategoriId,
                            'wartawan_id'    => $wartawan->id,
                            'tanggal_terbit' => date('Y-m-d H:i:s', strtotime($post['date'])),
                            'keterangan'     => 'Import dari WordPress',
                        ]
                    );
                    $totalImported++;
                }
                $page++; // Lanjut ke halaman berikutnya
            } else {
                // Jika error atau array kosong (berarti sudah mencapai halaman terakhir)
                $hasNextPage = false; 
            }
        }

        $this->info("Selesai! Berhasil mengimpor total {$totalImported} artikel.");
    }
}