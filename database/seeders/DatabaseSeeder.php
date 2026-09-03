<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Insert Data Admin Default
        $adminId = DB::table('users')->insertGetId([
            'username' => 'admin_kanal',
            'password' => Hash::make('kanal_kalimantan_777'), // Ubah sesuai kebutuhan
            'name' => 'Administrator',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Insert Data Pengaturan Bonus Awal (sesuai spesifikasi)
        DB::table('pengaturan_bonus')->insert([
            'minimal_views' => 3000,
            'nominal_bonus' => 50000.00,
            'updated_by' => $adminId, // Relasi ke admin yang baru dibuat
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}