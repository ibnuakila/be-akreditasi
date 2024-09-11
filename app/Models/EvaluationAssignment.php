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
        'schedule_date',
        'methode',
        'accreditation_id',
        'accreditaion_proposal_id',
        'sent_date',
        'expire_date',
        'assignment_state_id'
    ];
}
