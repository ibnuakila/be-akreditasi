<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccreditationProposal;
use Illuminate\Http\Request;

class SurveilanceAssignmentController extends BaseController
{
    public function list(Request $request)
    {
        $is_assessor = false;
        if ($request->hasHeader('Access-User')) {
            $temp_request_header = $request->header('Access-User');
            $request_header = str_replace('\"', '"', $temp_request_header);
            $request_header = json_decode($request_header, true);
            $user_id = $request_header['id'];
            $roles = $request_header['roles'];
            foreach ($roles as $role) {
                if ($role['name'] == 'Asesor' || $role['name'] == 'ASSESOR') {
                    $is_assessor = true;
                }
            }

            if ($is_assessor) {
                // $user_access = $request_header;
                // $query = AccreditationProposal::query()
                //     //->select('accreditation_proposals.*')
                //     ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                //     ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                //     ->join('evaluation_assignments', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                //     ->join('assessors', 'evaluation_assignments.assessor_id', '=', 'assessors.id')
                //     ->whereIn('accreditation_proposals.proposal_state_id', [2,3])                
                //     ->Where('institution_requests.status', '=', 'valid')
                //     ->where('assessors.user_id', '=', $user_id)
                //     ->select([
                //         'accreditation_proposals.*',
                //         //'evaluation_assignments.*',
                //         'proposal_date',
                //         'proposal_states.state_name',
                //         'institution_requests.category',
                //         'library_name',
                //         'npp',
                //         'agency_name',
                //         'institution_head_name',
                //         'institution_requests.email',
                //         'telephone_number',
                //         'province_name as province',
                //         'city_name as city',
                //         'subdistrict_name as subdistrict',
                //         'village_name as village'
                //     ]);
                //     if ($s = $request->input(key: 'search')) { //filter berdasarkan name            
                //         $query->where('institution_requests.library_name', 'like', "%{$s}%")
                //             ->orWhere('institution_requests.agency_name', 'like', "%{$s}%");
                //     }
                //     if ($s = $request->input(key: 'province')) { //filter berdasarkan name            
                //         $query->where('province_name', '=', "{$s}");
                //     }
                //     if ($s = $request->input(key: 'city')) { //filter berdasarkan name            
                //         $query->where('city_name', '=', "{$s}");
                //     }
                //     if ($s = $request->input(key: 'subdistrict')) { //filter berdasarkan name            
                //         $query->where('subdistrict_name', '=', "{$s}");
                //     }
                //     if ($s = $request->input(key: 'state_name')) { //filter berdasarkan name            
                //         $query->where('proposal_states.state_name', '=', "{$s}");
                //     }
                //     $query->orderBy('proposal_date','desc');
                //     $perPage = $request->input(key: 'pageSize', default: 10);
                //     $page = $request->input(key: 'page', default: 1);
                //     $total = $query->count();
                //     $response = $query->offset(value: ($page - 1) * $perPage)
                //         ->limit($perPage)
                //         ->paginate();

            } else {
                $query = AccreditationProposal::query()
                    ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                    ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                    ->where('proposal_state_id', '=', 4)
                    ->where('is_valid', '=', 'valid')
                    ->Where('institution_requests.status', '=', 'valid')
                    ->select([
                        'accreditation_proposals.*',
                        'proposal_date',
                        'proposal_states.state_name',
                        'institution_requests.category',
                        'library_name',
                        'npp',
                        'agency_name',
                        'institution_head_name',
                        'email',
                        'telephone_number',
                        'province_name as province',
                        'city_name as city',
                        'subdistrict_name as subdistrict',
                        'village_name as village'
                    ]);
                if ($s = $request->input(key: 'search')) { //filter berdasarkan name            
                    $query->where('institution_requests.library_name', 'like', "%{$s}%")
                        ->orWhere('institution_requests.agency_name', 'like', "%{$s}%");
                }
                if ($s = $request->input(key: 'province')) { //filter berdasarkan name            
                    $query->where('province_name', '=', "{$s}");
                }
                if ($s = $request->input(key: 'city')) { //filter berdasarkan name            
                    $query->where('city_name', '=', "{$s}");
                }
                if ($s = $request->input(key: 'subdistrict')) { //filter berdasarkan name            
                    $query->where('subdistrict_name', '=', "{$s}");
                }
                if ($s = $request->input(key: 'state_name')) { //filter berdasarkan name            
                    $query->where('proposal_states.state_name', '=', "{$s}");
                }
                $query->orderBy('proposal_date', 'desc');
                $perPage = $request->input(key: 'pageSize', default: 10);
                $page = $request->input(key: 'page', default: 1);
                $total = $query->count();
                $response = $query->offset(value: ($page - 1) * $perPage)
                    ->limit($perPage)
                    ->paginate();
            }
            return $this->sendResponse($response, "Success", $total);
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }
}
