<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Artikel;
use App\Models\KategoriBerita;
use App\Models\Wartawan;

class WordPressSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan parameter ?_embed untuk menarik Penulis & Kategori secara bersamaan
        $postsResponse = Http::get('https://kanalkalimantan.com/wp-json/wp/v2/posts?per_page=30&_embed');

        if ($postsResponse->successful()) {
            $count = 0;
            foreach ($postsResponse->json() as $post) {
                
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
                $count++;
            }
            $this->command->info("Berhasil mengimpor {$count} artikel dengan kategori & penulis asli!");
        }
    }
}