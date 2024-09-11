<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccreditationProposalFiles extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $fillable = [
        'accreditation_proposal_id',
        'file_name',
        'file_type',
        'file_path',
        'proposal_document_id',
        'validation',
        'notes'
    ];

    public function proposalDocuments()
    {
        return $this->hasMany(ProposalDocument::class);
    }
}
