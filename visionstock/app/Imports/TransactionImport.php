<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    use Importable;

    public const HEADINGS = [
        'nama_produk' => 'Nama Produk',
        'stok_terjual' => 'Stok Terjual',
        'tanggal_terjual' => 'Tanggal Terjual',
    ];

    private int $importedCount = 0;

    /**
     * @var array<int, array{row: int, messages: array<int, string>}>
     */
    private array $failures = [];

    public function __construct(private readonly int $userId) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            $rowNumber = $index + 2;

            if (count(array_filter($data, fn (mixed $value): bool => $value !== null && $value !== '')) === 0) {
                continue;
            }

            if (is_string($data['nama_produk'] ?? null)) {
                $data['nama_produk'] = trim($data['nama_produk']);
            }
            $data['tanggal_terjual'] = $this->normalizeDate($data['tanggal_terjual'] ?? null);

            $validator = Validator::make($data, [
                'nama_produk' => [
                    'required',
                    'string',
                    Rule::exists('products', 'name')->where('user_id', $this->userId),
                ],
                'stok_terjual' => ['required', 'integer', 'min:1'],
                'tanggal_terjual' => ['required', 'date_format:Y-m-d'],
            ], [
                'nama_produk.required' => 'Nama produk wajib diisi.',
                'nama_produk.string' => 'Nama produk harus berupa teks.',
                'nama_produk.exists' => 'Produk tidak ditemukan pada data stok akun ini.',
                'stok_terjual.required' => 'Stok terjual wajib diisi.',
                'stok_terjual.integer' => 'Stok terjual harus berupa bilangan bulat.',
                'stok_terjual.min' => 'Stok terjual minimal 1.',
                'tanggal_terjual.required' => 'Tanggal terjual wajib diisi.',
                'tanggal_terjual.date_format' => 'Tanggal terjual harus berformat YYYY-MM-DD.',
            ]);

            if ($validator->fails()) {
                $this->addFailure($rowNumber, $validator->errors()->all());

                continue;
            }

            $product = Product::query()
                ->where('user_id', $this->userId)
                ->where('name', $data['nama_produk'])
                ->lockForUpdate()
                ->first();
            $quantity = (int) $data['stok_terjual'];

            if ($product === null) {
                $this->addFailure($rowNumber, ['Produk tidak ditemukan pada data stok akun ini.']);

                continue;
            }

            if ($quantity > $product->stock) {
                $this->addFailure(
                    $rowNumber,
                    ["Stok terjual melebihi stok tersedia ({$product->stock}). Transaksi tidak disimpan."],
                );

                continue;
            }

            $product->decrement('stock', $quantity);
            Transaction::create([
                'user_id' => $this->userId,
                'product_id' => $product->id,
                'quantity_sold' => $quantity,
                'sold_at' => $data['tanggal_terjual'].' 00:00:00',
            ]);

            $this->importedCount++;
        }
    }

    /**
     * @return array<int, array{row: int, messages: array<int, string>}>
     */
    public function failures(): array
    {
        return $this->failures;
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * @param  array<int, string>  $messages
     */
    private function addFailure(int $row, array $messages): void
    {
        $this->failures[] = [
            'row' => $row,
            'messages' => array_values(array_unique($messages)),
        ];
    }

    private function normalizeDate(mixed $date): mixed
    {
        if (is_numeric($date) && (float) $date > 0 && (float) $date <= 2_958_465) {
            return Date::excelToDateTimeObject((float) $date)->format('Y-m-d');
        }

        return $date;
    }
}
