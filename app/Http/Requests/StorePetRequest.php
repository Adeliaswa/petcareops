<?php

namespace App\Http\Requests;

use App\Models\DurationFactor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'resepsionis']) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->whereNull('deleted_at'),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'spesies' => ['required', Rule::in(['anjing', 'kucing'])],
            'ras' => ['nullable', 'string', 'max:100'],
            'ukuran' => [
                'required',
                Rule::in(
                    DurationFactor::where('kategori', 'ukuran')
                        ->pluck('key')
                        ->all()
                ),
            ],
            'jenis_bulu' => [
                'required',
                Rule::in(
                    DurationFactor::where('kategori', 'jenis_bulu')
                        ->pluck('key')
                        ->all()
                ),
            ],
            'perilaku' => [
                'nullable',
                Rule::in(
                    DurationFactor::where('kategori', 'perilaku')
                        ->pluck('key')
                        ->all()
                ),
            ],
            'alergi' => ['nullable', 'string', 'max:1000'],
            'catatan_kesehatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('perilaku')) {
            $this->merge(['perilaku' => 'normal']);
        }
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Pemilik hewan wajib dipilih.',
            'customer_id.exists' => 'Data pemilik tidak ditemukan.',
            'nama.required' => 'Nama hewan wajib diisi.',
            'spesies.required' => 'Jenis hewan wajib dipilih.',
            'spesies.in' => 'Jenis hewan harus anjing atau kucing.',
            'ukuran.required' => 'Ukuran hewan wajib dipilih.',
            'ukuran.in' => 'Ukuran hewan tidak tersedia.',
            'jenis_bulu.required' => 'Jenis bulu wajib dipilih.',
            'jenis_bulu.in' => 'Jenis bulu tidak tersedia.',
            'perilaku.in' => 'Kategori perilaku tidak tersedia.',
        ];
    }
}