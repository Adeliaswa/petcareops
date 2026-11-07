<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Services\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'resepsionis']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('no_wa');

        if (blank($phone)) {
            return;
        }

        try {
            $this->merge([
                'no_wa' => app(PhoneNormalizer::class)->normalize($phone),
            ]);
        } catch (InvalidArgumentException) {
            $this->merge([
                'no_wa' => '__invalid_phone__',
            ]);
        }
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        if (! $customer instanceof Customer) {
            $customer = Customer::find($customer);
        }

        return [
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => [
                'required',
                'string',
                'regex:/^62[0-9]{8,13}$/',
                Rule::unique('customers', 'no_wa')->ignore($customer?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pemilik wajib diisi.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid.',
            'no_wa.unique' => 'Nomor WhatsApp sudah terdaftar.',
        ];
    }
}