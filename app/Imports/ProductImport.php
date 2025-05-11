<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts, WithChunkReading
{
    public function model(array $row)
    {
        return new Product([
            'unique_key' => clean_utf8($row['unique_key']),
            'product_title' => clean_utf8($row['product_title']),
            'product_description' => clean_utf8($row['product_description']),
            'style' => clean_utf8($row['style']),
            'color_name' => clean_utf8($row['color_name']),
            'size' => clean_utf8($row['size']),
            'piece_price' => clean_utf8($row['piece_price']),
            'sanmar_mainframe_color' => clean_utf8($row['sanmar_mainframe_color']),
        ]);
    }

    public function uniqueBy()
    {
        return 'unique_key';
    }

    public function batchSize(): int
    {
        return 5000;
    }

    public function chunkSize(): int
    {
        return 5000;
    }
}
