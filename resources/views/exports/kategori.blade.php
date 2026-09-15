<table>
    @foreach($kategoris as $kategori)
        <!-- Judul Kategori -->
        <tr>
            <th colspan="4" style="background-color: #f7ca5e; font-weight: bold; text-align: left; font-size: 14px; border: 1px solid #000000;">
                KATEGORI: {{ strtoupper($kategori->nama_kategori ?? $kategori->nama) }}
            </th>
        </tr>
        
        <!-- Header Tabel Berita -->
        <tr>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">JUDUL BERITA</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">LINK TAUTAN</th>
            <th style="background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;">TANGGAL TERBIT</th>
        </tr>

        <!-- Looping Isi Berita di Dalam Kategori Tersebut -->
        @if($kategori->artikel && $kategori->artikel->count() > 0)
            @foreach($kategori->artikel as $index => $artikel)
            <tr>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                    {{ $index + 1 }}
                </td>
                <td style="border: 1px solid #000000; vertical-align: top; color: #000000;">
                    {{ $artikel->judul }}
                </td>
                <!-- Kolom C dibiarkan kosong karena diisi otomatis oleh RichText di Controller/Export Class -->
                <td style="border: 1px solid #000000; vertical-align: top;">
                    
                </td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: top;">
                    {{ $artikel->tanggal_terbit ? \Carbon\Carbon::parse($artikel->tanggal_terbit)->translatedFormat('d M Y') : '-' }}
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="4" style="border: 1px solid #000000; text-align: center; font-style: italic;">
                    Belum ada berita di kategori ini.
                </td>
            </tr>
        @endif

        <!-- Spasi kosong pemisah antar kategori -->
        <tr>
            <td colspan="4"></td>
        </tr>
    @endforeach
</table>