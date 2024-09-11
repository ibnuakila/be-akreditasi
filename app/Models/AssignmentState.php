<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentState extends Model
{
    use HasFactory;
    protected $timestamps = false;
    protected $table = 'assignment_states';
    protected $fillable = [
        'state_name'
    ];
}
