<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EvaluationRecomendation extends Model
{
    use HasFactory;
    use HasUuids;
    public $timestamps = false;
    protected $table = 'evaluation_recomendations';
    protected $fillable = [
        'main_component_id',
        'nama',
        'content',
        'evaluation_id'
    ];
}
