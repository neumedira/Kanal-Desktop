<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;

    protected $table = 'bonus';

    protected $fillable = [
        'artikel_id',
        'wartawan_id',
        'periode_bulan',
        'periode_tahun',
        'views_saat_dihitung',
        'minimal_views_saat_itu',
        'nominal_bonus_saat_itu',
        'total_bonus',
        'sumber',
        'ditambahkan_oleh',
    ];

    public function artikel()
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }

    public function wartawan()
    {
        return $this->belongsTo(Wartawan::class, 'wartawan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'ditambahkan_oleh');
    }
}
