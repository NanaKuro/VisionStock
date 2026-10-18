<?php

namespace App\Http\Controllers;

use App\Exports\StockTemplateExport;
use App\Http\Requests\StockImportRequest;
use App\Imports\StockImport;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetReaderException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StockImportController extends Controller
{
    public function index(): View
    {
        return view('stok.import');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new StockTemplateExport,
            'template-stok-visionstock.xlsx',
        );
    }

    public function import(StockImportRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('file');

        abort_unless($user instanceof User && $file instanceof UploadedFile, 403);

        try {
            $headings = Excel::toArray(new HeadingRowImport, $file)[0][0] ?? [];
            $missingHeadings = array_diff(
                array_keys(StockImport::HEADINGS),
                array_values($headings),
            );

            if ($missingHeadings !== []) {
                $missingLabels = array_map(
                    fn (string $heading): string => StockImport::HEADINGS[$heading],
                    $missingHeadings,
                );

                return back()->withErrors([
                    'file' => 'Kolom wajib belum lengkap: '.implode(', ', $missingLabels).'. Unduh template untuk melihat format yang benar.',
                ]);
            }

            $import = new StockImport($user->id);
            DB::transaction(fn () => Excel::import($import, $file));
        } catch (LaravelExcelException|SpreadsheetReaderException $exception) {
            Log::warning('File Excel stok tidak dapat dibaca.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return back()->withErrors([
                'file' => 'File Excel tidak dapat dibaca. Pastikan berkas tidak rusak dan gunakan template VisionStock.',
            ]);
        }

        $failures = $import->failures()
            ->groupBy(fn (Failure $failure): int => $failure->row())
            ->map(fn ($rowFailures, $row): array => [
                'row' => $row,
                'messages' => $rowFailures
                    ->flatMap(fn (Failure $failure): array => $failure->errors())
                    ->unique()
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return redirect()
            ->route('stok.import')
            ->with('status', $import->importedCount() > 0
                ? "Impor selesai. {$import->importedCount()} produk berhasil ditambahkan."
                : 'Tidak ada produk yang berhasil diimpor.')
            ->with('stock_import_result', [
                'imported_count' => $import->importedCount(),
                'failures' => $failures,
            ]);
    }
}
