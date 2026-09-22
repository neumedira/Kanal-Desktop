<table>
    <!-- Baris 1: Judul Utama -->
    <tr>
        <td>BONUS BERITA BULAN {{ strtoupper($namaBulan) }} {{ $tahun }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <!-- Baris 2: Kosong (Karena di-merge dengan baris 1 di PhpSpreadsheet) -->
    <tr>
        <td colspan="5"></td>
    </tr>

    <!-- Baris 3: Header Tabel -->
    <tr>
        <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f2f2f2;">NO</th>
        <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f2f2f2;">JUDUL BERITA</th>
        <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f2f2f2;">VIEWER</th>
        <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f2f2f2;">PEWARTA</th>
        <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #f2f2f2;">BONUS</th>
    </tr>

    <!-- Isi Data -->
    @foreach($bonuses as $index => $item)
    <tr>
        <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">{{ $index + 1 }}</td>
        <!-- Kolom B dikosongkan karena diisi otomatis oleh RichText PHP -->
        <td style="border: 1px solid #000000; vertical-align: top;"></td>
        <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">{{ $item->views_saat_dihitung }}</td>
        <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">{{ $item->wartawan->nama ?? '-' }}</td>
        <td style="border: 1px solid #000000; text-align: right; vertical-align: top;">{{ $item->total_bonus }}</td>
    </tr>
    @endforeach
</table>