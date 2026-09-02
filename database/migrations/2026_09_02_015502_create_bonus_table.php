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
    Schema::create('bonus', function (Blueprint $table) {
        $table->id();
        $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
        $table->foreignId('wartawan_id')->constrained('wartawan')->restrictOnDelete();
        $table->unsignedTinyInteger('periode_bulan');
        $table->unsignedSmallInteger('periode_tahun');
        $table->unsignedInteger('views_saat_dihitung');
        $table->unsignedInteger('minimal_views_saat_itu');
        $table->decimal('nominal_bonus_saat_itu', 12, 2);
        $table->decimal('total_bonus', 12, 2);
        $table->enum('sumber', ['manual', 'otomatis'])->default('otomatis');
        $table->foreignId('ditambahkan_oleh')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();

        $table->unique(['artikel_id', 'periode_bulan', 'periode_tahun'], 'bonus_artikel_periode_unique');
        $table->index(['periode_bulan', 'periode_tahun']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus');
    }
};
