<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccreditationProposal;
use App\Models\Assessor;
use App\Models\EvaluationAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalAssignmentController extends BaseController
{
    public function addNew($id){
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
    public function destroy($id){
        $evaluation = EvaluationAssignment::find($id);
        if(is_object($evaluation)){
            if($evaluation->assignment_state_id == 1){
                $evaluation->delete();
                return $this->sendResponse([], "Success", 1);
            }else{
                return $this->sendError("Error", "Proposal Sedang dinilai, tidak dapat dihapus!", 500);
            }
            
        }
    }

	public function index(){

    }

    public function list(Request $request){
        $query = AccreditationProposal::query()
            ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
            ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
            ->where('accreditation_proposals.proposal_state_id', '=', 2)
            //->where('accreditation_proposals.is_valid', '=', 'valid')
            ->Where('institution_requests.status', '=', 'valid')
            ->select(['accreditation_proposals.*',
                'evaluation_assignments.*',
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
                'village_name as village']);
        if ($s = $request->input(key: 'search')) {//filter berdasarkan name            
            $query->where('institution_requests.library_name', 'like', "%{$s}%");
        }
        if ($s = $request->input(key: 'province')) {//filter berdasarkan name            
            $query->where('province_name', '=', "{$s}");
        }
        if ($s = $request->input(key: 'city')) {//filter berdasarkan name            
            $query->where('city_name', '=', "{$s}");
        }
        if ($s = $request->input(key: 'subdistrict')) {//filter berdasarkan name            
            $query->where('subdistrict_name', '=', "{$s}");
        }
        if ($s = $request->input(key: 'state_name')) {//filter berdasarkan name            
            $query->where('proposal_states.state_name', '=', "{$s}");
        }
        $perPage = $request->input(key: 'pageSize', default: 10);
        $page = $request->input(key: 'page', default: 1);
        $total = $query->count();
        $response = $query->offset(value: ($page - 1) * $perPage)
            ->limit($perPage)
            ->paginate();
        return $this->sendResponse($response, "Success", $total);
    }

	/**
	 * 
	 * @param $id
	 */
	public function edit($id){
        $evaluation = EvaluationAssignment::find($id);
        if(is_object($evaluation)){
            return $this->sendResponse($evaluation, 'Success');
        }else{
            return $this->sendError('Error', 'Object not found');
        }
    }

	/**
	 * 
	 * @param $request
	 */
	public function store(Request $request){
        $input = $request->all();
        $valid = Validator::make($input, [
            'accreditation_proposal_id' => 'required',
            'method' => 'required',
            'scheduled_date' => 'required',
            'expired_date' => 'required',
            'assessor_id' => 'required'
        ]);
        if ($valid->fails()) {
            return $this->sendError('Error',$valid->errors());
        }

        $data = [
            'accreditation_proposal_id' => $input['accreditation_proposal_id'],
            'method' => $input['method'],
            'scheduled_date' => $input['scheduled_date'],
            'expired_date' => $input['expire_date'],
            'assessor_id' => $input['assessor_id'],
            'assignment_state_id' => 1
        ];
        $create = EvaluationAssignment::create($data);
        //update accreditation proposal
        $accreProposal = AccreditationProposal::find($input['accreditation_proposal_id']);
        $accreProposal->proposal_state_id = 2;
        $temp = $accreProposal->assignment_count;
        $accreProposal->assignment_count = $temp++;
        $accreProposal->save();
        return $this->sendResponse($create,'Success', 1);
    }

	/**
	 * 
	 * @param $request
	 * @param $model
	 */
	public function update(Request $request, $id){
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
            return $this->sendError('Error',$valid->errors());
        }

        $data = [
            'accreditation_proposal_id' => $input['accreditation_proposal_id'],
            'method' => $input['method'],
            'scheduled_date' => $input['scheduled_date'],
            'expired_date' => $input['expired_date'],
            'assessor_id' => $input['assessor_id'],
            'assignment_state_id' => 1
        ];
        if(is_object($evaluation)){
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
        return $this->sendResponse($evaluation,'Success', 1);
    }
}
