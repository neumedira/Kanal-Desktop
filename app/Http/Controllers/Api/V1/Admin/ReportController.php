<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function export(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));
        
        $fileName = "Laporan_Bonus_{$bulan}_{$tahun}.csv";
        
        $bonus = Bonus::with(['artikel', 'wartawan'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($bonus) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Wartawan', 'Judul Berita', 'Tanggal Terbit', 'Views Saat Dihitung', 'Total Bonus', 'Sumber']);

            foreach ($bonus as $row) {
                fputcsv($file, [
                    $row->wartawan->nama,
                    $row->artikel->judul,
                    $row->artikel->tanggal_terbit->format('Y-m-d'),
                    $row->views_saat_dihitung,
                    $row->total_bonus,
                    ucfirst($row->sumber)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}