<?php

namespace App\Exports;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArtikelExport implements FromView, WithStyles
{
    protected $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function view(): View
    {
        // Ambil data artikel yang dicentang beserta relasinya
        $artikels = Artikel::with(['kategori', 'wartawan'])->whereIn('id', $this->ids)->get();

        return view('exports.artikel', [
            'artikels' => $artikels
        ]);
    }

    // PERBAIKAN: Menambahkan tipe return ": array" dan me-return []
    public function styles(Worksheet $sheet): array
    {
        // Mengatur agar teks di kolom B (Tema/Link) bisa turun ke bawah (Wrap Text)
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        
        // Mengatur lebar kolom secara manual agar rapi seperti gambar
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);

        return []; // Wajib ditambahkan agar tidak error tipe data
    }
}