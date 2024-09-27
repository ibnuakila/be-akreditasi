<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalDocument extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $table = 'proposal_documents';
    protected $fillable = [
        'document_name',
        'option',
        'document_format',
        'instrument_id',
        'instrument_component_id'
    ];

    public function accreditationProposalFiles(){
        return $this->hasMany(AccreditationProposalFiles::class);
    }
}
