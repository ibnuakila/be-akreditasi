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
}
