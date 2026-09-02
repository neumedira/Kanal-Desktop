<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanBonus extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_bonus';

    protected $fillable = [
        'minimal_views',
        'nominal_bonus',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
