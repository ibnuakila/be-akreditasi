<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterFeedback extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'master_feedbacks';
    protected $fillable = [
        'feedback'        
    ];

    public function accreditationFeedback()
    {
        return $this->hasOne(AccreditationFeedback::class);
    }
}
