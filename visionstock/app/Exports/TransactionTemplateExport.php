<?php

namespace App\Exports;

use App\Imports\TransactionImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionTemplateExport implements FromArray, WithHeadings
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
        return array_values(TransactionImport::HEADINGS);
    }
}
