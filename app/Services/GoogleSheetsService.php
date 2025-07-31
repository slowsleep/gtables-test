<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use App\Models\Record;
use Illuminate\Support\Collection;

class GoogleSheetsService
{
    protected Sheets $service;
    protected string $spreadsheetId;

    public function __construct($spreadsheetId = null)
    {
        $this->spreadsheetId = $spreadsheetId;

        $client = new Client();
        $client->setAuthConfig(config('services.google.credentials_path'));
        $client->addScope(Sheets::SPREADSHEETS);
        $client->setAccessType('offline');

        $this->service = new Sheets($client);
    }

    public function syncRecords($toAdd, $toDelete, $toUpdate)
    {
        // Ограничиваем количество запросов до 60 в минуту
        $batchSize = 60;
        $delayBetweenBatches = 60; // 60 секунд между партиями

        // Добавление новых строк
        if ($toAdd->isNotEmpty()) {
            $this->appendRecords($toAdd);
        }

        // Удаление строк
        if (!empty($toDelete)) {
            $this->deleteRecords($toDelete);
        }

        // Обновление строк
        if ($toUpdate->isNotEmpty()) {
            $batches = $toUpdate->chunk($batchSize);

            foreach ($batches as $index => $batch) {
                if ($index > 0) {
                    sleep($delayBetweenBatches);
                }

                foreach ($batch as $record) {
                    $this->updateRecordPreservingComment($record);
                }
            }
        }


    }

    public function getAllSheetData()
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A2:Z');
        $rows = $response->getValues() ?? [];

        return collect($rows);
    }

    public function appendRecords(Collection $records)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A2:Z');
        $rows = $response->getValues() ?? [];

        $lastRowIndex = count($rows) + 1;

        $values = [];
        foreach ($records as $record) {
            $values[] = [$record->id, $record->title, $record->status, $record->created_at, $record->updated_at];
        }

        $body = new ValueRange(['values' => $values]);

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            "A{$lastRowIndex}",
            $body,
            ['valueInputOption' => 'RAW']
        );

    }

    protected function findRecordRowRange($recordId)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A2:A');
        $rows = $response->getValues();

        foreach ($rows as $index => $row) {
            if ($row[0] == $recordId) {
                $rowNumber = $index + 2; // +2 потому что заголовки и нумерация с 1
                return "A{$rowNumber}:E{$rowNumber}";
            }
        }

        return null;
    }

    protected function findRecordRow($recordId)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A2:A');
        $rows = $response->getValues();

        foreach ($rows as $index => $row) {
            if ($row[0] == $recordId) {
                return $index + 2;
            }
        }

        return null;
    }

    public function updateRecordPreservingComment(Record $record)
    {
        $rowNumber = $this->findRecordRow($record->id);

        if (!$rowNumber) return;

        $existingRow = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            "A{$rowNumber}:Z{$rowNumber}"
        )->getValues()[0] ?? [];

        $comment = $existingRow[5] ?? ''; // n+1 столбец

        $values = [[
            $record->id,
            $record->title,
            $record->status,
            $record->created_at,
            $record->updated_at,
            $comment
        ]];

        $body = new ValueRange(['values' => $values]);

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            "A{$rowNumber}",
            $body,
            ['valueInputOption' => 'RAW']
        );
    }

    public function updateOrAppendRecord(Record $record)
    {
        $range = $this->findRecordRowRange($record->id);

        $values = [
            [$record->id, $record->title, $record->status, $record->created_at, $record->updated_at]
        ];

        $body = new ValueRange(['values' => $values]);

        if ($range) {
            // Обновляем строку
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'RAW']
            );
        } else {
            // Добавляем строку
            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                'A1',
                $body,
                ['valueInputOption' => 'RAW']
            );
        }
    }

    public function deleteRecord($recordId)
    {
        $row = $this->findRecordRow($recordId);

        if (!$row) return;

        $batchUpdateRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                [
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => 0,
                            'dimension' => 'ROWS',
                            'startIndex' => $row - 1,
                            'endIndex' => $row
                        ]
                    ]
                ]
            ]
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }

    public function deleteRecords(array $recordIds)
    {
        // Получаем список всех ID из колонки A (начиная со 2-й строки)
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, 'A2:A');
        $rows = $response->getValues();

        $rowIndexesToDelete = [];

        foreach ($rows as $index => $row) {
            if (in_array($row[0], $recordIds)) {
                // +2 потому что index с нуля и первая строка — заголовок
                $rowNumber = $index + 2;
                $rowIndexesToDelete[] = $rowNumber;
            }
        }

        if (empty($rowIndexesToDelete)) return;

        // Важно: удалять с конца, чтобы индексы не сдвигались
        rsort($rowIndexesToDelete);

        $requests = [];

        foreach ($rowIndexesToDelete as $rowIndex) {
            $requests[] = [
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => 0,
                        'dimension' => 'ROWS',
                        'startIndex' => $rowIndex - 1,
                        'endIndex' => $rowIndex,
                    ]
                ]
            ];
        }

        $batchRequest = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchRequest);
    }

}
