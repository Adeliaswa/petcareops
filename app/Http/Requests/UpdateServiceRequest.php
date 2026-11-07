<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'durasi_dasar' => ['required', 'integer', 'min:1'],
            'harga' => ['required', 'numeric', 'min:0'],
            'aktif' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama layanan wajib diisi.',
            'durasi_dasar.required' => 'Durasi dasar wajib diisi.',
            'durasi_dasar.min' => 'Durasi dasar harus lebih dari 0 menit.',
            'harga.required' => 'Harga layanan wajib diisi.',
            'harga.min' => 'Harga layanan tidak boleh negatif.',
            'aktif.required' => 'Status layanan wajib ditentukan.',
        ];
    }
}