<?php

namespace App\Http\Requests\Api\Bonus;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // <-- Wajib true agar diizinkan lewat
    }

    public function rules(): array
    {
        return [
            'minimal_views' => 'required|integer|min:0',
            'nominal_bonus' => 'required|numeric|min:0',
        ];
    }
}
