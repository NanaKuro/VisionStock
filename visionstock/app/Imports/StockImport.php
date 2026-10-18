<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class StockImport implements OnEachRow, SkipsEmptyRows, SkipsOnFailure, WithHeadingRow, WithValidation
{
    use Importable;
    use SkipsFailures;

    public const HEADINGS = [
        'nama_produk' => 'Nama Produk',
        'deskripsi_produk' => 'Deskripsi Produk',
        'stok_produk' => 'Stok Produk',
        'jenis_produk' => 'Jenis Produk',
    ];

    private int $importedCount = 0;

    public function __construct(private readonly int $userId) {}

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'nama_produk' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->where('user_id', $this->userId),
            ],
            'deskripsi_produk' => ['nullable', 'string'],
            'stok_produk' => ['required', 'integer', 'min:0'],
            'jenis_produk' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationMessages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'nama_produk.string' => 'Nama produk harus berupa teks.',
            'nama_produk.max' => 'Nama produk maksimal 255 karakter.',
            'nama_produk.unique' => 'Nama produk tersebut sudah digunakan.',
            'deskripsi_produk.string' => 'Deskripsi produk harus berupa teks.',
            'stok_produk.required' => 'Stok produk wajib diisi.',
            'stok_produk.integer' => 'Stok produk harus berupa bilangan bulat.',
            'stok_produk.min' => 'Stok produk tidak boleh negatif.',
            'jenis_produk.string' => 'Jenis produk harus berupa teks.',
            'jenis_produk.max' => 'Jenis produk maksimal 255 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationAttributes(): array
    {
        return [
            'nama_produk' => 'Nama Produk',
            'deskripsi_produk' => 'Deskripsi Produk',
            'stok_produk' => 'Stok Produk',
            'jenis_produk' => 'Jenis Produk',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $row, int $index): array
    {
        foreach (['nama_produk', 'deskripsi_produk', 'jenis_produk'] as $field) {
            if (is_string($row[$field] ?? null)) {
                $row[$field] = trim($row[$field]);
            }
        }

        return $row;
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $categoryName = trim((string) ($data['jenis_produk'] ?? ''));
        $categoryId = null;

        if ($categoryName !== '') {
            $categoryId = ProductCategory::query()
                ->firstOrCreate([
                    'user_id' => $this->userId,
                    'name' => $categoryName,
                ])
                ->id;
        }

        Product::create([
            'user_id' => $this->userId,
            'category_id' => $categoryId,
            'name' => $data['nama_produk'],
            'description' => $data['deskripsi_produk'] ?? null,
            'stock' => (int) $data['stok_produk'],
        ]);

        $this->importedCount++;
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }
}
