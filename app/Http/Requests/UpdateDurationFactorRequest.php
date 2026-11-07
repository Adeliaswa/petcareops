<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDurationFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'multiplier' => ['required', 'numeric', 'gt:0'],
            'extra_minutes' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Nama faktor wajib diisi.',
            'multiplier.required' => 'Multiplier wajib diisi.',
            'multiplier.gt' => 'Multiplier harus lebih dari 0.',
            'extra_minutes.required' => 'Tambahan durasi wajib diisi.',
            'extra_minutes.min' => 'Tambahan durasi tidak boleh negatif.',
        ];
    }
}