<?php

namespace Tests\Feature;

use App\Exports\TransactionTemplateExport;
use App\Imports\TransactionImport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Tests\TestCase;

class TransactionImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_transaction_history_offers_import_action(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('transaksi.index'))
            ->assertOk()
            ->assertSee('Impor Excel')
            ->assertSee('Input Manual')
            ->assertSee(route('transaksi.import'));
    }

    public function test_user_can_open_import_page_and_download_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('transaksi.import'))
            ->assertOk()
            ->assertSee('Input Transaksi')
            ->assertSee('Impor Manual')
            ->assertSee('Stok Terjual')
            ->assertSee('Bukti Invoice')
            ->assertSee('Tanggal Terjual')
            ->assertSee(route('transaksi.import.template'));

        $this->actingAs($user)
            ->get(route('transaksi.import.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=template-impor-transaksi-visionstock.xlsx');

        $headings = Excel::toArray(
            new HeadingRowImport,
            UploadedFile::fake()->createWithContent(
                'template.xlsx',
                Excel::raw(new TransactionTemplateExport, ExcelWriter::XLSX),
            ),
        )[0][0];

        $this->assertSame(array_keys(TransactionImport::HEADINGS), array_values($headings));
    }

    public function test_import_saves_only_valid_transactions_and_deducts_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 12,
        ]);
        $otherUser = User::factory()->create();
        Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Produk Rahasia',
            'stock' => 7,
        ]);

        $this->actingAs($user)
            ->post(route('transaksi.import.store'), [
                'file' => $this->workbook([
                    ['Beras', 8, '2026-10-25'],
                    ['Beras', 5, '2026-10-26'],
                    ['Beras', 2, Date::dateTimeToExcel(new \DateTimeImmutable('2026-10-26'))],
                    ['Produk Rahasia', 1, '2026-10-26'],
                    ['Beras', 0, 'tanggal salah'],
                ]),
            ])
            ->assertRedirect(route('transaksi.import'))
            ->assertSessionHas('transaction_import_result.imported_count', 2)
            ->assertSessionHas('transaction_import_result.failures.0.row', 3)
            ->assertSessionHas('transaction_import_result.failures.1.row', 5)
            ->assertSessionHas('transaction_import_result.failures.2.row', 6)
            ->assertSessionHas('transaction_import_result.failures.0.messages.0', 'Stok terjual melebihi stok tersedia (4). Transaksi tidak disimpan.');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity_sold' => 8,
            'sold_at' => '2026-10-25 00:00:00',
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity_sold' => 2,
            'sold_at' => '2026-10-26 00:00:00',
        ]);
        $this->assertDatabaseCount('transactions', 2);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 2,
        ]);
    }

    public function test_manual_transaction_saves_invoice_and_deducts_stock(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Teh',
            'stock' => 10,
        ]);

        $this->actingAs($user)
            ->post(route('transaksi.import.manual'), [
                'product_id' => $product->id,
                'quantity_sold' => 4,
                'sold_at' => '2026-10-27',
                'invoice' => UploadedFile::fake()->create('invoice.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('transaksi.import'))
            ->assertSessionHas('transaction_success', 'Transaksi Berhasil Disimpan');

        $transaction = Transaction::firstOrFail();
        $this->assertSame($product->id, $transaction->product_id);
        $this->assertSame(4, $transaction->quantity_sold);
        $this->assertNotNull($transaction->invoice_path);
        Storage::disk('public')->assertExists($transaction->invoice_path);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 6,
        ]);
    }

    public function test_manual_transaction_is_rejected_if_quantity_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Kopi',
            'stock' => 3,
        ]);

        $this->actingAs($user)
            ->from(route('transaksi.import'))
            ->post(route('transaksi.import.manual'), [
                'product_id' => $product->id,
                'quantity_sold' => 4,
                'sold_at' => '2026-10-27',
            ])
            ->assertRedirect(route('transaksi.import'))
            ->assertSessionHasErrors([
                'quantity_sold' => 'Stok terjual melebihi stok tersedia (3). Transaksi tidak disimpan.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }

    public function test_import_rejects_workbooks_with_missing_headers(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('transaksi.import'))
            ->post(route('transaksi.import.store'), [
                'file' => $this->workbook(
                    [],
                    ['Nama Produk', 'Stok Terjual'],
                ),
            ])
            ->assertRedirect(route('transaksi.import'))
            ->assertSessionHasErrors([
                'file' => 'Kolom wajib belum lengkap: Tanggal Terjual. Unduh template untuk melihat format yang benar.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>|null  $headings
     */
    private function workbook(array $rows, ?array $headings = null): UploadedFile
    {
        $export = new class($rows, $headings ?? array_values(TransactionImport::HEADINGS)) implements FromArray, WithHeadings
        {
            /**
             * @param  array<int, array<int, mixed>>  $rows
             * @param  array<int, string>  $headings
             */
            public function __construct(
                private readonly array $rows,
                private readonly array $headings,
            ) {}

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return UploadedFile::fake()->createWithContent(
            'transaksi.xlsx',
            Excel::raw($export, ExcelWriter::XLSX),
        );
    }
}
