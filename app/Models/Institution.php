<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable = [
        'library_name',
        'agency_name',
        'region_id',
        'province_id',
        'city_id',
        'subdistrict_id',
        'village_id'
    ];

}
