<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\GoogleSheetSettings;

class GoogleSheetController extends Controller
{
    public function setSpreadsheetId(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $url = $request->input('url');

            $spreadsheetId = explode('/', $url)[5];

            $settings = GoogleSheetSettings::updateOrCreate(['id' => 1], [
                'spreadsheet_id' => $spreadsheetId,
            ])->fresh();

            Artisan::call('google-sheet:sync');

            return response()->json([
                'message' => 'ID Google-таблицы успешно обновлён!',
                'data' => $settings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
