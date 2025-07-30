<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetSettings extends Model
{
    use HasFactory;

    protected $table = 'google_sheet_settings';

    protected $fillable = [
        'spreadsheet_id',
    ];
}
