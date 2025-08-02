<?php
namespace App\Observers;

use App\Models\Record;
use App\Services\GoogleSheetsService;
use App\Models\GoogleSheetSettings;

class RecordObserver
{
    public function deleted(Record $record)
    {
        // $settings = GoogleSheetSettings::find(1);
        $settings = GoogleSheetSettings::latest()->first();

        if (!$settings || !$settings->spreadsheet_id) {
            return;
        }

        $service = new GoogleSheetsService($settings->spreadsheet_id);
        $service->deleteRecord($record->id);
    }

    public function updated(Record $record)
    {
        if ($record->wasChanged('status')) {
            $settings = GoogleSheetSettings::latest()->first();

            if (!$settings || !$settings->spreadsheet_id) return;

            $service = new GoogleSheetsService($settings->spreadsheet_id);

            if ($record->status === 'Allowed') {
                $service->updateOrAppendRecord($record);
            } elseif ($record->status === 'Prohibited') {
                $service->deleteRecord($record->id);
            }
        }
    }

}
