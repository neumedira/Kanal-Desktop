<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('artikel', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('wp_post_id')->unique();
        $table->foreignId('kategori_id')->constrained('kategori_berita')->restrictOnDelete();
        $table->foreignId('wartawan_id')->constrained('wartawan')->restrictOnDelete();
        $table->string('judul', 255);
        $table->string('link', 500);
        $table->date('tanggal_terbit');
        $table->unsignedInteger('total_views')->default(0);
        $table->text('keterangan')->nullable();
        $table->timestamp('last_synced_at')->nullable();
        $table->timestamps();

        $table->index('link');
        $table->index('tanggal_terbit');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
