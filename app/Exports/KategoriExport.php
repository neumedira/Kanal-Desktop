<?php

namespace App\Exports;

use App\Models\KategoriBerita;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;

class KategoriExport implements FromView, WithStyles
{
    protected $ids;
    protected $kategoris;

    public function __construct($ids)
    {
        $this->ids = $ids;
        // Pindahkan query ke konstruktor agar bisa diakses oleh view() dan styles()
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

    public function styles(Worksheet $sheet): array
    {
        // Atur lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(60); // Kolom Link
        $sheet->getColumnDimension('D')->setWidth(20);

        // Looping untuk mewarnai teks link di kolom C menjadi Biru + Garis Bawah
        $currentRow = 1;
        foreach ($this->kategoris as $kategori) {
            $currentRow++; // Baris Judul Kategori
            $currentRow++; // Baris Header Tabel (NO, JUDUL, LINK, TANGGAL)

            if ($kategori->artikel && $kategori->artikel->count() > 0) {
                foreach ($kategori->artikel as $artikel) {
                    $currentRow++; // Baris data artikel
                    
                    // Terapkan RichText khusus pada Kolom C (Link Tautan)
                    $richText = new RichText();
                    $linkText = $richText->createTextRun($artikel->link ?? '');
                    $linkText->getFont()->setColor(new Color(Color::COLOR_BLUE));
                    $linkText->getFont()->setUnderline(true);

                    $sheet->setCellValue('C' . $currentRow, $richText);
                }
            } else {
                $currentRow++; // Baris "Belum ada berita"
            }

            $currentRow++; // Baris spasi kosong antar kategori
        }

        return [];
    }
}   