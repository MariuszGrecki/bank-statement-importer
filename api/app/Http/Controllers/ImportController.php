<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use Illuminate\Http\Request;
use App\Models\Import;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImportRequest $request)
    {
        $file = $request->file('file');

        $key = $file->store('imports', 's3');

        $importResource = $request->user()->imports()->create(
            [
                'bank_account_id' => $request->validated('bank_account_id'),
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $key
            ]
        );

        return new ImportResource($importResource);
    }

    public function preview(Import $import)
    {
        Gate::authorize('view', $import);

        $file = Storage::disk('s3')->readStream($import->file_path);


        if ($file !== false) {
            while (($wiersz = fgetcsv($file, 0, ';')) !== false) {
                print_r($wiersz);
            }

            fclose($file);
        }

        return new ImportResource($import);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
