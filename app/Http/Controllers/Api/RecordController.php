<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Record;

class RecordController extends Controller
{

    public function store(Request $request){
        try {
            $request->validate([
                'title' => 'required',
                'status' => 'required|in:allowed,prohibited',
            ]);
            $newRecord = Record::create($request->all());

            return response()->json(['record' => $newRecord], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function show($id) {
        try {
            $record = Record::findOrFail($id);

            return response()->json(['record' => $record], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function update(Request $request, $id){
        try {
            $request->validate([
                'title' => 'required',
                'status' => 'required|in:allowed,prohibited',
            ]);
            $record = Record::findOrFail($id);
            $record->update($request->all());

            return response()->json(['record' => $record], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function destroy($id){
        try {
            $record = Record::findOrFail($id);
            $record->delete();

            return response()->json(['message' => 'Record deleted successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

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
