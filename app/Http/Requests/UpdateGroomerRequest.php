<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'level_skill' => ['required', 'string', 'max:30'],
            'bisa_kucing' => ['required', 'boolean'],
            'bisa_agresif' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'level_skill.required' => 'Tingkat keahlian wajib diisi.',
            'bisa_kucing.required' => 'Kemampuan menangani kucing wajib ditentukan.',
            'bisa_agresif.required' => 'Kemampuan menangani hewan agresif wajib ditentukan.',
        ];
    }
}