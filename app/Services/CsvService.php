<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class CsvService
{
    /**
     * Parse CSV data from uploaded file
     */
    public function parseCsv($file): array
    {
        $path = $file->getRealPath();
        $data = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                fclose($handle);
                throw new \Exception('Invalid CSV file: No headers found');
            }

            // Clean headers (remove BOM and whitespace)
            $headers = array_map(function ($header) {
                return trim(str_replace("\xEF\xBB\xBF", '', $header));
            }, $headers);

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== count($headers)) {
                    continue; // Skip malformed rows
                }

                $rowData = array_combine($headers, $row);
                $rowData['_row_number'] = $rowNumber;
                $data[] = $rowData;
            }

            fclose($handle);
        }

        return $data;
    }

    /**
     * Validate CSV data against rules
     */
    public function validateCsvData(array $data, array $rules): array
    {
        $errors = [];
        $validData = [];

        foreach ($data as $index => $row) {
            $rowNumber = $row['_row_number'] ?? ($index + 2);
            unset($row['_row_number']);

            $validator = Validator::make($row, $rules);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $validator->errors()->toArray(),
                ];
            } else {
                $validData[] = $validator->validated();
            }
        }

        return [
            'valid_data' => $validData,
            'errors' => $errors,
            'total_rows' => count($data),
            'valid_rows' => count($validData),
            'error_rows' => count($errors),
        ];
    }

    /**
     * Generate CSV file from data
     */
    public function generateCsv(array $data, array $headers, string $filename): string
    {
        $path = storage_path("app/exports/{$filename}");

        // Ensure exports directory exists
        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $handle = fopen($path, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($handle, $headers);

        // Write data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $csvRow[] = $row[$header] ?? '';
            }
            fputcsv($handle, $csvRow);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Generate CSV template
     */
    public function generateTemplate(array $headers, ?array $exampleRow = null): string
    {
        $filename = 'template_'.time().'.csv';
        $path = storage_path("app/exports/{$filename}");

        // Ensure exports directory exists
        if (! file_exists(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }

        $handle = fopen($path, 'w');

        // Add UTF-8 BOM for Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($handle, $headers);

        // Write example row if provided
        if ($exampleRow) {
            fputcsv($handle, $exampleRow);
        }

        fclose($handle);

        return $path;
    }
}
