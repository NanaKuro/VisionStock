<?php

namespace App\Http\Controllers;

use App\Exports\TransactionTemplateExport;
use App\Http\Requests\TransactionImportRequest;
use App\Http\Requests\TransactionStoreRequest;
use App\Imports\TransactionImport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionImportController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.import', [
            'products' => $request->user()->products()->orderBy('name')->get(),
        ]);
    }

    public function store(TransactionStoreRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $data = $request->validated();
        DB::transaction(function () use ($data, $request, $user): void {
            $product = Product::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($data['product_id']);
            $quantity = (int) $data['quantity_sold'];

            if ($quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity_sold' => "Stok terjual melebihi stok tersedia ({$product->stock}). Transaksi tidak disimpan.",
                ]);
            }

            $invoicePath = $request->file('invoice')?->store(
                "users/{$user->id}/invoices",
                'public',
            );

            Transaction::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity_sold' => $quantity,
                'sold_at' => $data['sold_at'],
                'invoice_path' => $invoicePath,
            ]);

            $product->decrement('stock', $quantity);
        });

        return redirect()
            ->route('transaksi.import')
            ->with('transaction_success', 'Transaksi Berhasil Disimpan');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new TransactionTemplateExport,
            'template-impor-transaksi-visionstock.xlsx',
        );
    }

    public function import(TransactionImportRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('file');

        abort_unless($user instanceof User && $file instanceof UploadedFile, 403);

        try {
            $headings = Excel::toArray(new HeadingRowImport, $file)[0][0] ?? [];
            $missingHeadings = array_diff(
                array_keys(TransactionImport::HEADINGS),
                array_values($headings),
            );

            if ($missingHeadings !== []) {
                $missingLabels = array_map(
                    fn (string $heading): string => TransactionImport::HEADINGS[$heading],
                    $missingHeadings,
                );

                return back()->withErrors([
                    'file' => 'Kolom wajib belum lengkap: '.implode(', ', $missingLabels).'. Unduh template untuk melihat format yang benar.',
                ]);
            }

            $import = new TransactionImport($user->id);
            DB::transaction(fn () => Excel::import($import, $file));
        } catch (LaravelExcelException|SpreadsheetReaderException $exception) {
            Log::warning('File Excel transaksi tidak dapat dibaca.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return back()->withErrors([
                'file' => 'File Excel tidak dapat dibaca. Pastikan berkas tidak rusak dan gunakan template VisionStock.',
            ]);
        }

        $failures = $import->failures();

        return redirect()
            ->route('transaksi.import')
            ->with('status', $import->importedCount() > 0
                ? "Impor selesai. {$import->importedCount()} transaksi berhasil ditambahkan."
                : 'Tidak ada transaksi yang berhasil diimpor.')
            ->with('transaction_import_result', [
                'imported_count' => $import->importedCount(),
                'failures' => $failures,
            ]);
    }
}
