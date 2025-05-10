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
            'unique_key' => $row['unique_key'],
            'product_title' => $row['product_title'],
            'product_description' => $row['product_description'],
            'style' => $row['style'],
            'color_name' => $row['color_name'],
            'size' => $row['size'],
            'piece_price' => $row['piece_price'],
            'sanmar_mainframe_color' => $row['sanmar_mainframe_color'],
        ]);
    }

    public function uniqueBy()
    {
        return 'unique_key';
    }

    public function batchSize(): int
    {
        return 1300;
    }

    public function chunkSize(): int
    {
        return 1300;
    }
}
