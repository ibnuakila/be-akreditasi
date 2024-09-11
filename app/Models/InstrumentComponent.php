<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumentComponent extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $table = 'instrument_components';
    protected $fillable = [
        'category',
        'name',
        'weight',
        'type',
        'order',
        'parent_id',
        'instrument_id'
    ];
}
