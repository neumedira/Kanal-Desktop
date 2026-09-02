<?php

namespace App\Services;

use App\Models\Artikel;
use App\Models\KategoriBerita;
use App\Models\Wartawan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WordPressService
{
    protected string $baseUrl;

    public function __construct()
    {
        // URL WordPress KanalKalimantan
        $this->baseUrl = config('services.wordpress.url', 'https://kanalkalimantan.com/wp-json/wp/v2');
    }

    /**
     * Sinkronisasi Kategori dari WP
     */
    public function syncKategori(): void
    {
        $response = Http::get("{$this->baseUrl}/categories", ['per_page' => 100]);

        if ($response->successful()) {
            foreach ($response->json() as $category) {
                // Menggunakan nama_kategori sebagai penanda unik agar tidak error saat ada nama ganda
                KategoriBerita::updateOrCreate(
                    ['nama_kategori' => $category['name']],
                    ['slug' => $category['slug']]
                );
            }
        }
    }

    /**
     * Sinkronisasi Wartawan (Authors) dari WP
     */
    public function syncWartawan(): void
    {
        $response = Http::get("{$this->baseUrl}/users", ['per_page' => 100]);

        if ($response->successful()) {
            foreach ($response->json() as $user) {
                Wartawan::updateOrCreate(
                    ['wp_author_id' => $user['id']],
                    ['nama' => $user['name']]
                );
            }
        }
    }

    /**
     * Sinkronisasi Artikel & Views dari WP
     */
    public function syncArtikel(int $page = 1, int $perPage = 20): void
    {
        $response = Http::get("{$this->baseUrl}/posts", [
            'page' => $page,
            'per_page' => $perPage,
            '_embed' => true,
        ]);

        if ($response->successful()) {
            foreach ($response->json() as $post) {
                // 1. Cari Wartawan berdasarkan wp_author_id
                $wartawan = Wartawan::where('wp_author_id', $post['author'])->first();

                // 2. Cari Kategori berdasarkan slug dari data _embedded WordPress
                $wpKategoriSlug = $post['_embedded']['wp:term'][0][0]['slug'] ?? null;
                $kategori = KategoriBerita::where('slug', $wpKategoriSlug)->first() ?? KategoriBerita::first();

                if ($wartawan) {
                    Artikel::updateOrCreate(
                        ['wp_post_id' => $post['id']],
                        [
                            'kategori_id'    => $kategori ? $kategori->id : 1,
                            'wartawan_id'    => $wartawan->id,
                            'judul'          => html_entity_decode($post['title']['rendered'], ENT_QUOTES, 'UTF-8'),
                            'link'           => $post['link'],
                            'tanggal_terbit' => date('Y-m-d', strtotime($post['date'])),
                            'total_views'    => $post['pageviews'] ?? $post['views'] ?? 0,
                            'last_synced_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
