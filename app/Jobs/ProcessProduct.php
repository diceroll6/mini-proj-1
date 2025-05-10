<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use App\Models\CsvUpload;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessProduct implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public CsvUpload $csv_upload,
    )
    {
        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->csv_upload->status = 'processing';
        $this->csv_upload->save();

        $file_hash = hash_file('md5', Storage::path($this->csv_upload->filepath) );

        if (CsvUpload::where('file_hash', $file_hash)->exists())
        {
            $this->csv_upload->delete();
            return;
        }

        $this->csv_upload->file_hash = $file_hash;

        try
        {
            Excel::import(new ProductImport, $this->csv_upload->filepath, $this->csv_upload->file_driver);
            $this->csv_upload->status = 'completed';
        }
        catch(Exception $e)
        {
            logger()->error($e->getMessage());
            $this->csv_upload->status = 'failed';
        }

        $this->csv_upload->save();
    }
}
