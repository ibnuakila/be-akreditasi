<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationAssignment;
use Illuminate\Http\Request;

class EvaluationController extends BaseController implements ICrud
{
    //
    /**
	 * 
	 * @param $model
	 */
	public function destroy($id){
        $evaluasi = EvaluationAssignment::find($id);
        if(is_object($evaluasi)){
            $ret = $evaluasi->delete();
            $this->sendResponse([], "Delete successful", $ret);
        }else{
            $this->sendError('Error', 'Delete fail');
        }    
    }

	public function index(){

    }

    public function list(Request $request)//with filter
    {
        $query = Evaluation::query()
            ->join('evaluation_assignments', 'evaluations.evaluation_assignment_id', '=', 'evaluation_assignments.id')
            ->join('accreditation_proposals', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
            ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
            ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')            
            ->join('assessors', 'assessors.id', '=', 'evaluations.assessor_id')
            ->join('instruments', 'accreditation_proposals.instrument_id', '=', 'instruments.id')
            ->select(['accreditation_proposals.proposal_date',
                //'evaluation_assignments.*',
                'evaluations.*',
                'proposal_states.state_name',
                'instruments.category',
                'assessors.name as assessor_name',
                'library_name',
                'npp',
                'agency_name',
                'institution_head_name',
                
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
	public function show($id){

    }

	/**
	 * 
	 * @param $request
	 */
	public function store(Request $request){

    }

	/**
	 * 
	 * @param $request
	 * @param $model
	 */
	public function update(Request $request, $id){

    }
}
