<?php

namespace App\Actions\Imports;

use App\Models\Import;
use Illuminate\Support\Facades\Storage;


class PreviewImportAction
{
    public function handle(Import $import): array
    {
        $file = Storage::disk('s3')->readStream($import->file_path);
        $titles = [];
        $rowSummary = [];

        if ($file !== false) {
            while (($row = fgetcsv($file, 0, ';')) !== false) {
                if (!$titles) {
                    $titles = $row;

                    continue;
                }

                if (count($rowSummary) < 5) {
                    $rowSummary[] = $row;
                } else {
                    break;
                }
            }

            fclose($file);
        }

        $data = [
            "headers" => $titles,
            "rows" => $rowSummary
        ];

        return $data;
    }
}
