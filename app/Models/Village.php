<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory;
    
    use HasUuids;
    protected $table = 'villages';
    protected $fillable = [
        'name',
        'city_id'
    ];
    public function institutionRequest()
    {
        return $this->hasMany(InstitutionRequest::class);
    }
}
