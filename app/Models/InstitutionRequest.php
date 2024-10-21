<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionRequest extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'institution_requests';

    protected $fillable = [
        'category',        
        'library_name',
        'agency_name',        
        'npp',
        'typology',
        'address',
        'region_id',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'subdistrict_id',
        'subdistrict_name',
        'village_id',
        'village_name',
        'institution_head_name',
        'email',
        'telephone_number',
        'mobile_number',
        'library_head_name',
        'library_worker_name',
        'registration_form_file',
        'title_count',
        'status',
        'last_predicate',
        'last_certification_date',
        'predicate',
        'accredited_at',
        'institution_id',
        'user_id',
        'accreditation_proposal_id'
    ];

    public function accreditationProposal()
    {
        return $this->belongsTo(AccreditationProposal::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
