<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Record;

class RecordController extends Controller
{
    public function generate(Request $request)
    {
        try {
            $count = $request->input('count', 10);
            $records = Record::factory()->count($count)->create();

            return response()->json(['records' => $records], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyAll()
    {
        try {
            Record::query()->delete();
            return response()->json(['message' => 'All records deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
