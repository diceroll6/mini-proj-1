<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Jobs\ProcessProduct;
use App\Models\CsvUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload', [
            'csv_upload_list' => CsvUpload::latest()->get(),
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'uploaded_file' => 'required|mimes:csv,txt'
        ]);

        $uploaded_file = $request->file('uploaded_file');

        $csv_upload = new CsvUpload();

        $csv_upload->uploaded_filename = $uploaded_file->getClientOriginalName();
        $csv_upload->status = 'pending';
        $csv_upload->file_driver = 'local';
        $csv_upload->filepath = $uploaded_file->store('temp-csvs');
        // $csv_upload->file_hash = hash_file('md5', Storage::path($csv_upload->filepath) );
        
        $csv_upload->save();

        ProcessProduct::dispatch($csv_upload);

        $csv_upload['time'] = $csv_upload->created_at?->format('Y-m-d g:ia');
        $csv_upload['time_human'] = $csv_upload->created_at?->diffForHumans();

        return [
            'row_data' => $csv_upload,
        ];
    }

    public function upload_row_info(Request $request, $id)
    {
        $csv_upload = CsvUpload::find($id);

        if(blank($csv_upload))
        {
            return [
                'status' => 'error',
            ];
        }

        return [
            'status' => $csv_upload->status,
        ];
    }
}
