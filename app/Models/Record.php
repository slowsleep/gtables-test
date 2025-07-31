<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\Status;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
    ];

    public function scopeAllowed()
    {
        return $this->where('status', Status::ALLOWED);
    }
}
