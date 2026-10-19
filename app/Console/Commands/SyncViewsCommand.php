<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use App\Services\WordPressService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class SyncViewsCommand extends Command
{
    protected $signature = 'views:sync
        {--days=31 : Hanya perbarui artikel yang terbit dalam N hari terakhir}
        {--concurrency=10 : Jumlah request paralel per batch}';

    protected $description = 'Mengambil jumlah views artikel dari plugin Post Views Counter (WordPress)';

    public function handle(): int
    {
        $siteUrl = rtrim(config('services.wordpress.site_url', 'https://kanalkalimantan.com'), '/');
        $days = max(1, (int) $this->option('days'));
        $concurrency = max(1, (int) $this->option('concurrency'));

        $query = Artikel::whereNotNull('wp_post_id')
            ->whereDate('tanggal_terbit', '>=', now()->subDays($days)->toDateString());

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn("Tidak ada artikel dalam {$days} hari terakhir.");
            return self::SUCCESS;
        }

        $this->info("Memperbarui views untuk {$total} artikel...");
        $updated = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById($concurrency, function ($artikels) use ($siteUrl, &$updated, &$failed) {
            $responses = Http::pool(function (Pool $pool) use ($artikels, $siteUrl) {
                return $artikels->map(fn ($a) => $pool
                    ->as((string) $a->id)
                    ->timeout(10)
                    ->get("{$siteUrl}/wp-json/post-views-counter/get-post-views/{$a->wp_post_id}")
                )->all();
            });

            foreach ($artikels as $artikel) {
                $response = $responses[(string) $artikel->id] ?? null;

                // Respons error / exception / bukan angka => lewati, JANGAN menimpa nilai lama dengan 0
                $views = ($response instanceof \Illuminate\Http\Client\Response && $response->successful())
                    ? WordPressService::parseViews($response->body())
                    : null;

                if ($views === null) {
                    $failed++;
                    continue;
                }

                $artikel->total_views = $views;
                $artikel->last_synced_at = now();
                $artikel->save();
                $updated++;
            }
        });

        $this->info("Selesai. Diperbarui: {$updated}, gagal/dilewati: {$failed}.");

        return $failed > 0 && $updated === 0 ? self::FAILURE : self::SUCCESS;
    }
}
