<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AccreditationContent extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $table = 'accreditation_contents';
    protected $fillable = [
        'aspect',
        'statement',
        'value',
        'type',
        'aspectable_id',
        'main_component_id',
        'instrument_aspect_point_id',
        'accreditation_proposal_id',
        'butir',                
    ];
    
}
