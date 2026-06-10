<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint, semua boleh
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Items wajib
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',

            // Shipping & Payment
            'shipping_method' => 'required|in:standard,express,same_day',
            'payment_method' => 'required|in:cod,midtrans',

            // Customer info - wajib untuk SEMUA (auth atau guest)
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^(\+62|0)[0-9]{9,12}$/',
            'address' => 'required|string|min:10|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|regex:/^[0-9]{5}$/',

            // Optional
            'notes' => 'nullable|string|max:500',
            'discount_code' => 'nullable|string|max:50',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi',
            'phone.required' => 'Nomor WhatsApp wajib diisi',
            'phone.regex' => 'Format nomor WhatsApp tidak valid (gunakan 62 atau 0)',
            'address.required' => 'Alamat pengiriman wajib diisi',
            'address.min' => 'Alamat terlalu pendek (minimal 10 karakter)',
            'city.required' => 'Kota wajib dipilih',
            'province.required' => 'Provinsi wajib dipilih',
            'postal_code.required' => 'Kode pos wajib diisi',
            'postal_code.regex' => 'Format kode pos tidak valid (5 digit)',
            'items.required' => 'Minimal ada 1 item di keranjang',
            'shipping_method.required' => 'Metode pengiriman wajib dipilih',
            'payment_method.required' => 'Metode pembayaran wajib dipilih',
        ];
    }
}