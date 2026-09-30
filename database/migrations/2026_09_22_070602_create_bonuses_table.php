<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_id')->constrained('artikels')->onDelete('cascade');
            $table->foreignId('wartawan_id')->constrained('wartawans')->onDelete('cascade');
            $table->integer('views_saat_dihitung')->default(0);
            $table->decimal('total_bonus', 15, 2)->default(0);
            $table->integer('bulan'); // Untuk filter bulan
            $table->integer('tahun');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonuses');
    }
};