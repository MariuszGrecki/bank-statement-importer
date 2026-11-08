<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use Illuminate\Http\Request;

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
