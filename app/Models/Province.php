<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'provinces';
    protected $fillable = [
        'name'
    ];

    public function institutionRequest()
    {
        return $this->hasMany(InstitutionRequest::class);
    }
}
