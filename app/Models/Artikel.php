<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';

    protected $fillable = [
        'wp_post_id',
        'kategori_id',
        'wartawan_id',
        'judul',
        'link',
        'tanggal_terbit',
        'total_views',
        'keterangan',
        'last_synced_at',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'last_synced_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi Database
    |--------------------------------------------------------------------------
    */

    public function kategori()
    {
        return $this->belongsTo(KategoriBerita::class, 'kategori_id');
    }

    public function wartawan()
    {
        return $this->belongsTo(Wartawan::class, 'wartawan_id');
    }

    public function bonus()
    {
        return $this->hasMany(Bonus::class, 'artikel_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Query (Penambahan Baru)
    |--------------------------------------------------------------------------
    */

    // Filter pencarian berdasarkan judul artikel
    public function scopeSearch($query, $keyword)
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where('judul', 'like', '%' . $keyword . '%');
        });
    }

    // Filter berdasarkan kategori
    public function scopeKategori($query, $kategoriId)
    {
        return $query->when($kategoriId, function ($q) use ($kategoriId) {
            $q->where('kategori_id', $kategoriId);
        });
    }

    // Filter berdasarkan rentang tanggal terbit
    public function scopeFilterTanggal($query, $startDate, $endDate)
    {
        return $query->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('tanggal_terbit', [$startDate, $endDate]);
        });
    }
}
