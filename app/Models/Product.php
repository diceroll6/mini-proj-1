<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'unique_key',
        'product_title',
        'product_description',
        'style',
        'color_name',
        'size',
        'piece_price',
        'sanmar_mainframe_color',
    ];

    protected $casts = [
        'unique_key' => 'string',
        'product_title' => 'string',
        'product_description' => 'string',
        'style' => 'string',
        'color_name' => 'string',
        'size' => 'string',
        'piece_price' => 'string',
        'sanmar_mainframe_color' => 'string',
    ];
}
