<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use App\Models\GoogleSheetSettings;

class FetchController extends Controller
{
    public function index($count = null)
    {
        $settings = GoogleSheetSettings::latest()->first();

        if (!$settings || !$settings->spreadsheet_id) {
            $this->error('Google Spreadsheet ID не задан.');
            return 0;
        }

        $googleSheetsService = new GoogleSheetsService($settings->spreadsheet_id);

        $sheetData = $count
            ? $googleSheetsService->getAllSheetData('A2:Z' . ($count + 1))
            : $googleSheetsService->getAllSheetData();

        return view('fetch', ['sheetData' => $sheetData, 'count' => $count]);
    }
}
