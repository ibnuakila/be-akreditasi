<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'type',
        'province_id'
    ];
}
