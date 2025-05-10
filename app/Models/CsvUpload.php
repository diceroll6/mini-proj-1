<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CsvUpload extends Model
{
    protected $table = 'csv_uploads';

    protected $fillable = [
        'uploaded_filename',
        'status',
        'file_driver',
        'filepath',
        'file_hash',
    ];

    protected $casts = [
        'uploaded_filename' => 'string',
        'status' => 'string',
        'file_driver' => 'string',
        'filepath' => 'string',
        'file_hash' => 'string',
    ];

    public static function list_status()
    {
        return [
            'pending',
            'processing',
            'failed',
            'completed',
        ];
    }

    public function status(): Attribute
    {
        return Attribute::make(
            get: fn(?string $val) => $val,
            set: function($val) {
                throw_if(!in_array($val, self::list_status()), 'pls use only '. implode(', ', self::list_status()));
                return $val;
            },
        );
    }

    public function nice_time()
    {
        return $this->created_at?->format('Y-m-d g:ia') . PHP_EOL . '(' . $this->created_at?->diffForHumans() . ')';
    }
}
