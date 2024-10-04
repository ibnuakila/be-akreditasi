<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'subdistricts';
    protected $fillable = [
        'name',
        'city_id'
    ];

    public function institutionRequest()
    {
        return $this->hasMany(InstitutionRequest::class);
    }
}
