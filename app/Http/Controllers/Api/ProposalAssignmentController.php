<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccreditationProposal;
use App\Models\Assessor;
use App\Models\EvaluationAssignment;
use App\Models\EvaluationAssignmentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalAssignmentController extends BaseController
{
    public function addNew($id)
    {
        $accreditation_proposal = AccreditationProposal::find($id);
        $assessor = Assessor::all();
        $data['accreditation_proposal'] = $accreditation_proposal;
        $data['assessor'] = $assessor;
        $data['method'] = [
            ['Online' => 'Online'],
            ['Onsite' => 'Onsite']
        ];
        return $this->sendResponse($data, "Success", 1);
    }
    public function destroy($id)
    {
        $evaluation = EvaluationAssignment::find($id);
        if (is_object($evaluation)) {
            if ($evaluation->assignment_state_id == 1) {
                $evaluation->delete();
                return $this->sendResponse([], "Success", 1);
            } else {
                return $this->sendError("Error", "Proposal Sedang dinilai, tidak dapat dihapus!", 500);
            }
        }
    }

    public function index() {}

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
        }
        if ($is_assessor) {
            $user_access = $request_header;
            $query = AccreditationProposal::query()
                //->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                ->join('evaluation_assignments', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                ->join('evaluation_assignment_user', 'evaluation_assignments.id', '=', 'evaluation_assignment_user.evaluation_assignment_id')
                ->join('assessors', 'evaluation_assignment_user.assessor_id', '=', 'assessors.id')
                ->whereIn('accreditation_proposals.proposal_state_id', [2,3])                
                ->Where('institution_requests.status', '=', 'valid')
                ->where('assessors.user_id', '=', $user_id)
                ->select([
                    'accreditation_proposals.*',
                    //'evaluation_assignments.*',
                    'proposal_date',
                    'proposal_states.state_name',
                    'institution_requests.category',
                    'library_name',
                    'npp',
                    'agency_name',
                    'institution_head_name',
                    'institution_requests.email',
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
                $query->orderBy('proposal_date','desc');
                $perPage = $request->input(key: 'pageSize', default: 10);
                $page = $request->input(key: 'page', default: 1);
                $total = $query->count();
                $response = $query->offset(value: ($page - 1) * $perPage)
                    ->limit($perPage)
                    ->paginate();
            
        } else {
            $query = AccreditationProposal::query()
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                ->where('proposal_state_id', '=', 2)
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
            $query->orderBy('proposal_date','desc');
            $perPage = $request->input(key: 'pageSize', default: 10);
            $page = $request->input(key: 'page', default: 1);
            $total = $query->count();
            $response = $query->offset(value: ($page - 1) * $perPage)
                ->limit($perPage)
                ->paginate();
        }
        return $this->sendResponse($response, "Success", $total);
    }

    /**
     * 
     * @param $id
     */
    public function edit($id)
    {
        $evaluation = EvaluationAssignment::find($id);
        if (is_object($evaluation)) {
            return $this->sendResponse($evaluation, 'Success');
        } else {
            return $this->sendError('Error', 'Object not found');
        }
    }

    /**
     * 
     * @param $request
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $valid = Validator::make($input, [
            'accreditation_proposal_id' => 'required',
            'method' => 'required',
            'scheduled_date' => 'required',
            'expired_date' => 'required',
            'assessor_id' => 'required'
        ]);
        if ($valid->fails()) {
            return $this->sendError('Error', $valid->errors());
        }

        $data = [
            'accreditation_proposal_id' => $input['accreditation_proposal_id'],
            'method' => $input['method'],
            'scheduled_date' => $input['scheduled_date'],
            'expired_date' => $input['expired_date'],
            //'assessor_id' => $input['assessor_id'],
            'assignment_state_id' => 1
        ];
        $temp_evaluation_assignment = EvaluationAssignment::where('accreditation_proposal_id', '=', $input['accreditation_proposal_id'])->first();
        if(!is_object($temp_evaluation_assignment)){
            $create = EvaluationAssignment::create($data);
        }else{
            $create = $temp_evaluation_assignment;
        }
        
        $data_assessor = [
            'evaluation_assignment_id' => $create->id,
            'assessor_id' => $input['assessor_id']
        ];
        if(is_object($create)){
            $assignment_user = EvaluationAssignmentUser::create($data_assessor);
        }
        
        //update accreditation proposal
        $accreProposal = AccreditationProposal::find($input['accreditation_proposal_id']);
        $accreProposal->proposal_state_id = 3;
        $temp = $accreProposal->assignment_count;
        $accreProposal->assignment_count = $temp + 1;
        $accreProposal->save();
        return $this->sendResponse($create, 'Success', 1);
    }

    /**
     * 
     * @param $request
     * @param $model
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $evaluation = EvaluationAssignment::find($id);
        $valid = Validator::make($input, [
            'accreditation_proposal_id' => 'required',
            'method' => 'required',
            'scheduled_date' => 'required',
            'expired_date' => 'required',
            'assessor_id' => 'required'
        ]);
        if ($valid->fails()) {
            return $this->sendError('Error', $valid->errors());
        }

        $data = [
            'accreditation_proposal_id' => $input['accreditation_proposal_id'],
            'method' => $input['method'],
            'scheduled_date' => $input['scheduled_date'],
            'expired_date' => $input['expired_date'],
            'assessor_id' => $input['assessor_id'],
            'assignment_state_id' => 1
        ];
        if (is_object($evaluation)) {
            $evaluation->accreditation_proposal_id = $input['accreditation_proposal_id'];
            $evaluation->method = $input['method'];
            $evaluation->scheduled_date = $input['scheduled_date'];
            $evaluation->expired_date = $input['expired_date'];
            $evaluation->assessor_id = $input['assessor_id'];
            $evaluation->update();
        }

        //update accreditation proposal
        $accreProposal = AccreditationProposal::find($input['accreditation_proposal_id']);
        $accreProposal->proposal_state_id = 2;
        $accreProposal->save();
        return $this->sendResponse($evaluation, 'Success', 1);
    }
}
