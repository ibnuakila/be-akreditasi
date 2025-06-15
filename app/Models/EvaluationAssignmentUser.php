<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAssignmentUser extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'evaluation_assignment_user';
    protected $fillable = [        
        'evaluation_assignment_id',
        'user_id',
        'assessor_id'
    ];
}
