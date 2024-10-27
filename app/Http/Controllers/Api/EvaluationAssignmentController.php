<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluasiAssignmentResource;
use App\Models\EvaluationAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationAssignmentController extends BaseController
{
    //
    public function destroy(EvaluationAssignment $model)
    {        
        //delete files
        if(is_object($model)){
            $model->delete();
        }
        
        return $this->sendResponse([], 'Evaluation Assignment Deleted!', $model->count());
    }

    public function index()
    {
        $evaluation = EvaluationAssignment::query()
            ->get();

        return $this->sendResponse(new EvaluasiAssignmentResource($evaluation), 'Success', $evaluation->count());
    }

    public function list(Request $request)//with filter
    {
        $query = EvaluationAssignment::query()
            ->join('accreditation_proposals', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
            ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
            ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
            
            ->select(['accreditation_proposals.proposal_date',
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
     * @param $id
     * 
     * @param id
     */
    public function show($id)
    {
        $evaluasi = EvaluationAssignment::find($id);            

        if (is_null($evaluasi)) {
            
            return $this->sendError('Proposal not found!');
        }
        return $this->sendResponse(new EvaluationAssignmentResource($accreditation), 'Proposal Available', $accreditation->count());
    }

    /**
     * @param $request
     * 
     * @param request
     */
    public function store(Request $request)
    {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            'institution_id' => 'required',
            'proposal_date' => 'required',
            'proposal_state_id' => 'required',
            'finish_date' => 'nullable',
            'type' => 'required',
            'notes' => 'nullable',
            'accredited_at' => 'nullable',
            'predicate' => 'nullable',
            'certificate_status' => 'nullable',
            'certificate_expires_at' => 'nullable',
            'pleno_date' => 'nullable',
            'certificate_file' => 'nullable',
            'recommendation_file' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $proposal = AccreditationProposal::create($input);
        return $this->sendResponse(new AccreditationProposalResource($proposal), 'Proposal Created', $proposal->count);
    }
}
