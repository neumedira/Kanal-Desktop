<table>
    <!-- Baris 1: Dikosongkan khusus untuk tempat gambar Banner -->
    <tr>
        <td colspan="5"></td>
    </tr>
    
    <!-- Baris Header (Kop Surat Excel) -->
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; font-size: 14px; background-color: #f7ca5e;">
            KANAL KALIMANTAN
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; font-size: 13px;">
            JADWAL PEMUATAN ADVERTORIAL
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; font-size: 13px;">
            kanalkalimantan.com
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold;">
            BULAN {{ strtoupper(\Carbon\Carbon::now()->translatedFormat('F Y')) }}
        </td>
    </tr>
    
    <!-- Spasi Kosong -->
    <tr>
        <td colspan="5"></td>
    </tr>

    <!-- Header Tabel (Dipisah menjadi 5 Kolom) -->
    <tr>
        <th style="background-color: #d9d9d9; font-weight: bold; text-align: center; border: 1px solid #000000;">NO</th>
        <th style="background-color: #d9d9d9; font-weight: bold; text-align: center; border: 1px solid #000000;">TEMA ADVERTORIAL</th>
        <th style="background-color: #d9d9d9; font-weight: bold; text-align: center; border: 1px solid #000000;">LINK BERITA</th>
        <th style="background-color: #d9d9d9; font-weight: bold; text-align: center; border: 1px solid #000000;">EDISI MUAT</th>
        <th style="background-color: #d9d9d9; font-weight: bold; text-align: center; border: 1px solid #000000;">KETERANGAN</th>
    </tr>

    <!-- Isi Data Tabel -->
    @foreach($artikels as $index => $item)
    <tr>
        <!-- Kolom NO -->
        <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
            {{ $index + 1 }}
        </td>
        
        <!-- Kolom JUDUL (Warna Hitam) -->
        <td style="border: 1px solid #000000; vertical-align: top; color: #000000;">
            {{ $item->judul }}
        </td>
        
        <!-- Kolom LINK (Warna Biru + Garis Bawah) -->
        <td style="border: 1px solid #000000; vertical-align: top;">
            <a href="{{ $item->link }}" style="color: #0000FF; text-decoration: underline;">{{ $item->link }}</a>
        </td>
        
        <!-- Kolom EDISI MUAT -->
        <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
            {{ \Carbon\Carbon::parse($item->tanggal_terbit)->translatedFormat('d F Y') }}
        </td>
        
        <!-- Kolom KETERANGAN -->
        <td style="border: 1px solid #000000; vertical-align: top;">
            {{ $item->keterangan ?? '' }}
        </td>
    </tr>
    @endforeach
</table>