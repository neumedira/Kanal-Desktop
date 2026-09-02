<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wartawan extends Model
{
    use HasFactory;

    protected $table = 'wartawan';

    protected $fillable = [
        'nama',
        'wp_author_id',
    ];

    public function artikel()
    {
        return $this->hasMany(Artikel::class, 'wartawan_id');
    }

    public function bonus()
    {
        return $this->hasMany(Bonus::class, 'wartawan_id');
    }
}
