<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccreditationFeedback extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'accreditation_feedbacks';
    protected $fillable = [
        'accreditation_proposal_id',
        'accreditation_date',
        'answer',
        'note',
        'master_feedback_id'
    ];

    public function accreditationProposal()
    {
        return $this->belongsTo(AccreditationProposal::class);
    }

    public function institutionRequest()
    {
        return $this->belongsTo(InstitutionRequest::class, 'accreditation_proposal_id', 'accreditation_proposal_id');
    }

    public function masterFeedback()
    {
        return $this->belongsTo(MasterFeedback::class);
    }
}
