<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StockImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx', 'extensions:xlsx', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Pilih berkas Excel yang akan diimpor.',
            'file.file' => 'Berkas impor tidak valid.',
            'file.mimes' => 'Berkas impor harus berformat .xlsx.',
            'file.extensions' => 'Berkas impor harus berformat .xlsx.',
            'file.max' => 'Ukuran berkas impor maksimal 10 MB.',
        ];
    }
}
