<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Record;
use App\Services\GoogleSheetsService;
use App\Models\GoogleSheetSettings;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-sheet:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация таблицы records в Google Sheets';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $settings = GoogleSheetSettings::latest()->first();

        if (!$settings || !$settings->spreadsheet_id) {
            Log::info('Google Spreadsheet ID не задан. Синхронизация пропущена.');
            return 0;
        }

        try {
            $records = Record::allowed()->get();

            $googleSheetsService = new GoogleSheetsService($settings->spreadsheet_id);
            $googleSheetData = $googleSheetsService->getAllSheetData();

            if ($googleSheetData->isEmpty() && $records->isEmpty()) {
                Log::info('Таблица Google Sheets и таблица records в БД - пусты. Синхронизация пропущена.');
                return 0;
            }

            $sheetData = $googleSheetData->keyBy(fn($row) => $row[0]); // key = record.id
            $dbData = $records->keyBy('id');

            $existingIds = $sheetData->keys()->all();
            $currentIds = $dbData->keys()->all();

            $toAdd = $dbData->except($existingIds);
            $toDelete = array_diff($existingIds, $currentIds);
            $toUpdate = $dbData->intersectByKeys($sheetData)->filter(function ($record, $id) use ($sheetData) {
                $sheetRow = $sheetData[$id];

                // Приводим даты к одинаковому формату для сравнения
                $sheetCreatedAt = isset($sheetRow[3]) ? date('Y-m-d H:i:s', strtotime($sheetRow[3])) : null;
                $sheetUpdatedAt = isset($sheetRow[4]) ? date('Y-m-d H:i:s', strtotime($sheetRow[4])) : null;

                return $record->title !== ($sheetRow[1] ?? null)
                    || $record->status !== ($sheetRow[2] ?? null)
                    || $record->created_at->format('Y-m-d H:i:s') != $sheetCreatedAt
                    || $record->updated_at->format('Y-m-d H:i:s') != $sheetUpdatedAt;
            });


            Log::info('Sync records', [
                'toAdd' => $toAdd->count(),
                'toDelete' => count($toDelete),
                'toUpdate' => $toUpdate->count()
            ]);

            if (!$toAdd->isNotEmpty() && empty($toDelete) && !$toUpdate->isNotEmpty()) {
                Log::info('Изменений нет, синхронизация пропущена');
                return 0;
            }

            $googleSheetsService->syncRecords($toAdd, $toDelete, $toUpdate);

            Log::info('Синхронизация завершена.');
        } catch (\Exception $e) {
            Log::error('Произошла ошибка при синхронизации: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
