<?php

namespace Tests\Feature;

use App\Exports\StockTemplateExport;
use App\Imports\StockImport;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Tests\TestCase;

class StockImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_import_page_offers_excel_template_and_manual_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('stok.import'))
            ->assertOk()
            ->assertSee('Unggah Excel')
            ->assertSee('Impor Manual')
            ->assertSee(route('stok.template'));
    }

    public function test_user_can_download_the_stock_template(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('stok.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=template-stok-visionstock.xlsx');

        $headings = Excel::toArray(
            new HeadingRowImport,
            UploadedFile::fake()->createWithContent(
                'template.xlsx',
                Excel::raw(new StockTemplateExport, ExcelWriter::XLSX),
            ),
        )[0][0];

        $this->assertSame(array_keys(StockImport::HEADINGS), array_values($headings));
    }

    public function test_import_adds_valid_rows_and_user_owned_product_categories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->products()->create([
            'name' => 'Beras',
            'stock' => 2,
        ]);
        $otherUser->productCategories()->create(['name' => 'Sembako']);

        $this->actingAs($user)
            ->post(route('stok.import.excel'), [
                'file' => $this->workbook([
                    ['Beras', 'Beras premium', 12, 'Sembako'],
                    ['Susu', '', 8, 'Minuman'],
                ]),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHas('stock_import_result.imported_count', 2)
            ->assertSessionHas('status', 'Impor selesai. 2 produk berhasil ditambahkan.');

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'Beras',
            'description' => 'Beras premium',
            'stock' => 12,
        ]);

        $this->assertDatabaseHas('product_categories', [
            'user_id' => $user->id,
            'name' => 'Sembako',
        ]);

        $this->assertDatabaseCount('products', 3);
        $this->assertDatabaseCount('product_categories', 3);
    }

    public function test_import_skips_invalid_rows_and_reports_the_excel_row_number(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('stok.import.excel'), [
                'file' => $this->workbook([
                    ['Beras', 'Beras premium', 12, 'Sembako'],
                    ['', 'Nama kosong', 'banyak', 'Sembako'],
                ]),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHas('stock_import_result.imported_count', 1)
            ->assertSessionHas('stock_import_result.failures.0.row', 3)
            ->assertSessionHas('stock_import_result.failures.0.messages.0', 'Nama produk wajib diisi.');

        $this->assertDatabaseCount('products', 1);
    }

    public function test_import_skips_duplicates_in_the_database_and_in_the_file(): void
    {
        $user = User::factory()->create();
        Product::create([
            'user_id' => $user->id,
            'name' => 'Sudah Ada',
            'stock' => 4,
        ]);

        $this->actingAs($user)
            ->post(route('stok.import.excel'), [
                'file' => $this->workbook([
                    ['Sudah Ada', '', 10, ''],
                    ['Telur', '', 5, ''],
                    ['Telur', '', 3, ''],
                ]),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHas('stock_import_result.imported_count', 1)
            ->assertSessionHas('stock_import_result.failures.0.row', 2)
            ->assertSessionHas('stock_import_result.failures.1.row', 4);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'Telur',
            'stock' => 5,
        ]);
    }

    public function test_import_rejects_a_workbook_with_missing_required_headers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('stok.import'))
            ->post(route('stok.import.excel'), [
                'file' => $this->workbook(
                    [],
                    ['Nama Produk', 'Stok Produk'],
                ),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHasErrors([
                'file' => 'Kolom wajib belum lengkap: Deskripsi Produk, Jenis Produk. Unduh template untuk melihat format yang benar.',
            ]);
    }

    public function test_import_reports_a_corrupt_workbook_without_creating_products(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('stok.import'))
            ->post(route('stok.import.excel'), [
                'file' => UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan file Excel'),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHasErrors([
                'file' => 'File Excel tidak dapat dibaca. Pastikan berkas tidak rusak dan gunakan template VisionStock.',
            ]);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_import_rejects_non_xlsx_files_and_files_over_ten_megabytes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('stok.import'))
            ->post(route('stok.import.excel'), [
                'file' => UploadedFile::fake()->create('stok.csv', 10, 'text/csv'),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHasErrors('file');

        $this->actingAs($user)
            ->from(route('stok.import'))
            ->post(route('stok.import.excel'), [
                'file' => UploadedFile::fake()->create(
                    'stok.xlsx',
                    10241,
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ),
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHasErrors([
                'file' => 'Ukuran berkas impor maksimal 10 MB.',
            ]);
    }

    public function test_manual_input_creates_a_product_and_its_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('stok.import.manual'), [
                'name' => 'Tepung',
                'description' => 'Tepung terigu',
                'stock' => 9,
                'category_name' => 'Bahan Kue',
            ])
            ->assertRedirect(route('stok.import'))
            ->assertSessionHas('status', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => 'Tepung',
            'stock' => 9,
        ]);

        $this->assertSame(
            $user->id,
            ProductCategory::where('name', 'Bahan Kue')->firstOrFail()->user_id,
        );
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>|null  $headings
     */
    private function workbook(array $rows, ?array $headings = null): UploadedFile
    {
        $export = new class($rows, $headings ?? array_values(StockImport::HEADINGS)) implements FromArray, WithHeadings
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
            'stok.xlsx',
            Excel::raw($export, ExcelWriter::XLSX),
        );
    }
}
