<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\GoogleSheetSettings;

class RecordController extends Controller
{
    public function index()
    {
        $records = Record::all();
        $googleSheetIdIsSet = GoogleSheetSettings::first();
        $googleSheetIdIsSet = $googleSheetIdIsSet ? $googleSheetIdIsSet->spreadsheet_id : false;
        
        return view('welcome', ['records' => $records, 'googleSheetIdIsSet' => $googleSheetIdIsSet]);
    }
}
