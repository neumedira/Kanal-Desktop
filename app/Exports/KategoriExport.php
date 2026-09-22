<?php

namespace App\Exports;

use App\Models\KategoriBerita;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;

class KategoriExport implements FromView, WithStyles, WithDrawings
{
    protected $ids;
    protected $kategoris;

    public function __construct($ids)
    {
        $this->ids = $ids;
        $this->kategoris = KategoriBerita::with(['artikel' => function($query) {
            $query->orderBy('tanggal_terbit', 'desc');
        }])->whereIn('id', $this->ids)->get();
    }

    public function view(): View
    {
        return view('exports.kategori', [
            'kategoris' => $this->kategoris
        ]);
    }

    // Menambahkan Gambar Banner
    public function drawings(): BaseDrawing|array
    {
        $drawing = new Drawing();
        $drawing->setName('Banner Kanal Kalimantan');
        $drawing->setDescription('Banner Header');
        $drawing->setPath(public_path('images/banner.png')); 
        $drawing->setWidth(900); // Sesuaikan angka ini jika kurang panjang/terlalu panjang
        $drawing->setCoordinates('A1');

        return $drawing;
    }

public function styles(Worksheet $sheet): array
    {
        // Tinggi baris pertama untuk tatakan gambar
        $sheet->getRowDimension(1)->setRowHeight(125);

        // Mengatur teks Judul (B) dan Link (C) bisa turun ke bawah (Wrap Text)
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);

        // Atur lebar kolom (Total 5 Kolom disamakan dengan Artikel)
        $sheet->getColumnDimension('A')->setWidth(5);  // NO
        $sheet->getColumnDimension('B')->setWidth(40); // JUDUL BERITA
        $sheet->getColumnDimension('C')->setWidth(40); // LINK TAUTAN
        $sheet->getColumnDimension('D')->setWidth(20); // TANGGAL TERBIT
        $sheet->getColumnDimension('E')->setWidth(20); // KETERANGAN (Tambahan)

        return [];
    }
}