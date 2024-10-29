<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAssignment extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'evaluation_assignments';
    protected $fillable = [        
        'method',
        'accreditation_id',
        'accreditation_proposal_id',
        'scheduled_date',
        'expired_date',
        'assignment_state_id',
        'assessor_id'
    ];
}
