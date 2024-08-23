<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalState extends Model
{
    use HasFactory;
    protected $table = 'proposal_state';
    protected $fillable = [
        'status_name'
    ];
}
