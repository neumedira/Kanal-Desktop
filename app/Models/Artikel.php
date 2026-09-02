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
}
