<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'evaluations';
    protected $timestamp = false;
    protected $fillable = [
        'file_path',
        'file_name',
        'file_type',
        'accreditation_proposal_id',
        'assessor_id',
        'evaluation_assignment_id'
    ];
}
