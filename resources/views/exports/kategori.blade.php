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
            KLASIFIKASI KATEGORI BERITA
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

    @foreach($kategoris as $kategori)
        <!-- Judul Kategori -->
        <tr>
            <th colspan="5" style="background-color: #f7ca5e; font-weight: bold; text-align: left; font-size: 14px; border: 1px solid #000000;">
                KATEGORI: {{ strtoupper($kategori->nama_kategori ?? $kategori->nama) }}
            </th>
        </tr>
        
        <!-- Header Tabel Berita -->
        <tr>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">JUDUL BERITA</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">LINK TAUTAN</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">TANGGAL TERBIT</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">KETERANGAN</th>
        </tr>

        <!-- Looping Isi Berita di Dalam Kategori Tersebut -->
        @if($kategori->artikel && $kategori->artikel->count() > 0)
            @foreach($kategori->artikel as $index => $artikel)
            <tr>
                <!-- Kolom NO -->
                <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                    {{ $index + 1 }}
                </td>
                
                <!-- Kolom JUDUL (Warna Hitam) -->
                <td style="border: 1px solid #000000; vertical-align: top; color: #000000;">
                    {{ $artikel->judul }}
                </td>
                
                <!-- Kolom LINK (Warna Biru + Garis Bawah langsung menggunakan tag a) -->
                <td style="border: 1px solid #000000; vertical-align: top;">
                    <a href="{{ $artikel->link }}" style="color: #0000FF; text-decoration: underline;">{{ $artikel->link }}</a>
                </td>
                
                <!-- Kolom TANGGAL TERBIT -->
                <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                    {{ $artikel->tanggal_terbit ? \Carbon\Carbon::parse($artikel->tanggal_terbit)->translatedFormat('d F Y') : '-' }}
                </td>
                
                <!-- Kolom KETERANGAN -->
                <td style="border: 1px solid #000000; vertical-align: top;">
                    {{ $artikel->keterangan ?? '' }}
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" style="border: 1px solid #000000; text-align: center; font-style: italic;">
                    Belum ada berita di kategori ini.
                </td>
            </tr>
        @endif

        <!-- Spasi kosong pemisah antar kategori -->
        <tr>
            <td colspan="5"></td>
        </tr>
    @endforeach
</table>