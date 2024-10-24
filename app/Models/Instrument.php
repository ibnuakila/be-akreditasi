<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'instruments';
    public $timeStamps = false;
    protected $fillable = [
        'category',
        'periode',
        'file_path',
        'file_name',
        'file_type',
        'is_active'
    ];
}
