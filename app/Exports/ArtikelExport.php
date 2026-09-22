<?php

namespace App\Exports;

use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing; // Tambahan wajib untuk mengatasi error

class ArtikelExport implements FromView, WithStyles, WithDrawings
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

    // PERBAIKAN: Menambahkan tipe return ": BaseDrawing|array" sesuai permintaan sistem
    // ... (kode atas tetap sama) ...

    public function drawings(): BaseDrawing|array
    {
        $drawing = new Drawing();
        $drawing->setName('Banner Kanal Kalimantan');
        $drawing->setDescription('Banner Header');
        
        $drawing->setPath(public_path('images/banner.png')); 
        
        // PERBAIKAN: Ganti setHeight menjadi setWidth agar melebar penuh
        // Angka 880 pixel ini estimasi pas untuk menutupi kolom A sampai E
        $drawing->setWidth(900); 
        
        $drawing->setCoordinates('A1'); 

        return $drawing;
    }

    public function styles(Worksheet $sheet): array
    {
        // PERBAIKAN: Tambah tinggi baris pertama karena gambarnya sekarang membesar
        // Anda bisa menaikkan/menurunkan angka 115 ini jika jarak ke teks di bawahnya kurang pas
        $sheet->getRowDimension(1)->setRowHeight(115);

        // Mengatur teks Judul (B) dan Link (C) bisa turun ke bawah (Wrap Text)
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        
        // Mengatur lebar untuk 5 kolom
        $sheet->getColumnDimension('A')->setWidth(5);  // NO
        $sheet->getColumnDimension('B')->setWidth(40); // TEMA ADVERTORIAL (Judul)
        $sheet->getColumnDimension('C')->setWidth(40); // LINK BERITA
        $sheet->getColumnDimension('D')->setWidth(20); // EDISI MUAT
        $sheet->getColumnDimension('E')->setWidth(20); // KETERANGAN

        return []; 
    }
}