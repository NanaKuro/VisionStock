<?php

namespace App\Exports;

use App\Imports\StockImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockTemplateExport implements FromArray, WithHeadings
{
    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return array_values(StockImport::HEADINGS);
    }
}
