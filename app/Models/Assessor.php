<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessor extends Model
{
    use HasFactory;
    protected $timpstamp = false;
    protected $table = 'assessors';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_id'
    ];
}
