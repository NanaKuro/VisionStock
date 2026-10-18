<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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

        $uniqueName = Rule::unique('products', 'name')
            ->where('user_id', $user->id);
        $productId = $this->route('product');

        if ($productId !== null) {
            $uniqueName->ignore($productId);
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')
                    ->where('user_id', $user->id),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Nama produk harus berupa teks.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            'name.unique' => 'Nama produk tersebut sudah digunakan.',
            'description.string' => 'Deskripsi produk harus berupa teks.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stock.min' => 'Stok produk tidak boleh negatif.',
            'category_name.string' => 'Jenis produk harus berupa teks.',
            'category_name.max' => 'Jenis produk maksimal 255 karakter.',
            'category_id.integer' => 'Jenis produk yang dipilih tidak valid.',
            'category_id.exists' => 'Jenis produk yang dipilih tidak tersedia.',
            'image.uploaded' => 'Gambar produk gagal diunggah. Silakan coba lagi.',
            'image.image' => 'Berkas gambar harus berupa gambar yang valid.',
            'image.mimes' => 'Gambar produk harus berformat JPG atau PNG.',
            'image.max' => 'Ukuran gambar maksimal 5 MB.',
        ];
    }
}
