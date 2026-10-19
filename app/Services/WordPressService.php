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
     * Ambil jumlah views satu artikel dari plugin Post Views Counter.
     * Endpoint mengembalikan angka polos (mis. 6). Jika beberapa ID digabung
     * dengan koma, hasilnya adalah JUMLAH total, jadi harus satu ID per request.
     *
     * @return int|null null jika request gagal / respons bukan angka
     *                  (agar nilai lama di database tidak tertimpa 0).
     */
    public function fetchViews(int $wpPostId): ?int
    {
        $siteUrl = rtrim(config('services.wordpress.site_url', 'https://kanalkalimantan.com'), '/');

        try {
            $response = Http::timeout(10)
                ->get("{$siteUrl}/wp-json/post-views-counter/get-post-views/{$wpPostId}");
        } catch (\Throwable $e) {
            Log::warning("fetchViews gagal untuk post {$wpPostId}: {$e->getMessage()}");
            return null;
        }

        return self::parseViews($response->successful() ? $response->body() : null);
    }

    /**
     * Ubah isi respons (mis. "6") menjadi integer, atau null bila bukan angka bulat.
     */
    public static function parseViews(?string $body): ?int
    {
        if ($body === null) {
            return null;
        }

        $clean = trim($body, " \t\n\r\0\x0B\"");

        return preg_match('/^\d+$/', $clean) ? (int) $clean : null;
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
                            // total_views sengaja TIDAK diisi di sini: REST API /wp/v2/posts
                            // tidak membawa data views. Views diisi oleh command `views:sync`.
                            'last_synced_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
