<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumentAspectPoint extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'instrument_aspect_points';
    protected $fillable = [
        'instrument_aspect_id',
        'statement',
        'value',
        'order'
    ];

    public function instrumentAspect()
    {
        return $this->belongsTo(InstrumentAspect::class);
    }
}
