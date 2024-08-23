<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'regions';
    protected $fillable = [
        'name',
        'created_by'
    ];
}
