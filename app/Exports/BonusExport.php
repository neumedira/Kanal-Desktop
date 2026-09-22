<?php

namespace App\Exports;

use App\Models\Bonus;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class BonusExport implements FromView, WithStyles
{
    protected $bulan;
    protected $bonuses;
    protected $namaBulan;
    protected $tahun;

    public function __construct($bulan)
    {
        $this->bulan = $bulan;
        $this->tahun = date('Y'); // Mengambil tahun saat ini
        $this->namaBulan = Carbon::createFromDate($this->tahun, $this->bulan, 1)->translatedFormat('F');

        // Ambil SEMUA data bonus pada bulan tersebut tanpa pagination
        $this->bonuses = Bonus::with(['wartawan', 'artikel'])
            ->where('periode_bulan', $this->bulan)
            ->get();
    }

    public function view(): View
    {
        return view('exports.bonus', [
            'bonuses'   => $this->bonuses,
            'namaBulan' => $this->namaBulan,
            'tahun'     => $this->tahun
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        // 1. Atur Lebar Kolom
        $sheet->getColumnDimension('A')->setWidth(5);  // NO
        $sheet->getColumnDimension('B')->setWidth(60); // JUDUL BERITA
        $sheet->getColumnDimension('C')->setWidth(12); // VIEWER
        $sheet->getColumnDimension('D')->setWidth(15); // PEWARTA
        $sheet->getColumnDimension('E')->setWidth(18); // BONUS

        // 2. Styling Judul (Merge A1 sampai E2)
        $sheet->mergeCells('A1:E2');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // 3. Format Wrap Text (Agar teks turun ke bawah)
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);

        // 4. Format Angka Ribuan & Mata Uang Rp
        $lastRow = count($this->bonuses) + 3;
        if ($lastRow >= 4) {
            // Kolom Viewer (C) - Angka biasa
            $sheet->getStyle('C4:C' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            // Kolom Bonus (E) - Mata Uang Rp
            $sheet->getStyle('E4:E' . $lastRow)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        }

        // 5. Trik RichText untuk Judul (Hitam) & Link (Biru)
        $row = 4; // Data dimulai dari baris ke-4
        foreach ($this->bonuses as $item) {
            $richText = new RichText();

            // Teks Judul
            $title = $richText->createTextRun(($item->artikel->judul ?? '-') . "\n");
            $title->getFont()->setColor(new Color(Color::COLOR_BLACK));

            // Teks Link
            $link = $richText->createTextRun($item->artikel->link ?? '-');
            $link->getFont()->setColor(new Color(Color::COLOR_BLUE));
            $link->getFont()->setUnderline(true);

            $sheet->setCellValue('B' . $row, $richText);
            $row++;
        }

        return [];
    }
}