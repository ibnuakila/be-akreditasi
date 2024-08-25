<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccreditationProposal extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'accreditation_proposals';
    public $timestamps = false;
    protected $fillable = [
        'institution_id',
        'proposal_date',
        'proposal_state_id',
        'finish_date',
        'type',
        'notes',
        'accredited_at',
        'predicate',
        'certificate_status',
        'certificate_sent_at',
        'certificate_expires_at',
        'pleno_date',
        'certificate_file',
        'recommendation_file'
    ];
}
