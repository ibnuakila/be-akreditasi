<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumentAspect extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $table = 'instrument_aspects';
    protected $fillable = [
        'aspect',
        'instrument_id',
        'instrument_component_id',
        'type',
        'order',
        'parent_id'
    ];

    public function instrumentComponent()
    {
        return $this->belongsTo(InstrumentComponent::class);
    }

    public function instrumentAspectPoint()
    {
        return $this->hasMany(InstrumentAspectPoint::class, 'instrument_aspect_id');
    }
}
