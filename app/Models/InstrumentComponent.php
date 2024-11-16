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

    public function instrument()
    {
        return $this->belongsTo(Instrument::class);
    }

    public function children()
    {
        return $this->hasMany(InstrumentComponent::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(InstrumentComponent::class, 'parent_id');
    }

    public function instrumentAspect()
    {
        return $this->hasMany(InstrumentAspect::class);
    }
}
