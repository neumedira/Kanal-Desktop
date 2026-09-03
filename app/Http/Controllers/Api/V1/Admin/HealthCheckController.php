<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait; // Memanggil trait standar tim dari Dev 4

class HealthCheckController extends Controller
{
    // Gunakan trait agar format JSON konsisten se-aplikasi
    use ApiResponseTrait;

    /**
     * Endpoint untuk mengecek status kesehatan API
     */
    public function index()
    {
        $data = [
            'service' => 'Backend API Kanal Kalimantan',
            'status' => 'Active',
            'timestamp' => now()->toDateTimeString(),
        ];

        // Memanggil method successResponse yang sudah disiapkan Kak Ihya
        return $this->successResponse($data, 'Health check passed. System is running perfectly.');
    }
}