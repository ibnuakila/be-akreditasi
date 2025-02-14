<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergedEvaluationContent extends Model
{
    use HasFactory;

    use HasUuids;

    protected $table = 'merged_evaluation_contents';
    protected $timestamp = false;
    protected $fillable = [
        'evaluation_id',
        'statement',
        'value',
        'comment',
        'pleno',
        'banding',
        'accreditation_content_id',
        'main_component_id',
        'instrument_aspect_point_id',
        'accreditation_proposal_id'
    ];

    public function instrumentAspectPoint()
    {
        return $this->belongsTo(InstrumentAspectPoint::class);
    }
}
