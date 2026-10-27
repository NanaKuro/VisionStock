<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $user = $this->user();

        abort_unless($user instanceof User, 403);

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('user_id', $user->id),
            ],
            'quantity_sold' => ['required', 'integer', 'min:1'],
            'sold_at' => ['required', 'date'],
            'invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Nama produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'quantity_sold.required' => 'Stok terjual wajib diisi.',
            'quantity_sold.integer' => 'Stok terjual harus berupa bilangan bulat.',
            'quantity_sold.min' => 'Stok terjual minimal 1.',
            'sold_at.required' => 'Tanggal terjual wajib diisi.',
            'sold_at.date' => 'Tanggal terjual tidak valid.',
            'invoice.file' => 'Bukti invoice tidak valid.',
            'invoice.mimes' => 'Bukti invoice harus berformat PDF, JPG, atau PNG.',
            'invoice.max' => 'Ukuran bukti invoice maksimal 5 MB.',
        ];
    }
}
