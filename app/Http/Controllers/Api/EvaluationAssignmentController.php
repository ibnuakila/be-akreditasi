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
        $query = EvaluationAssignment::query();
        if ($s = $request->input(key: 'name')) {//filter berdasarkan name 
            $query->join('accreditation_proposals', 'evaluation_assignments.accreditation_proposal_id', '=', 'accreditiona_proposals.id');
            $query->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accredition_proposal_id');
            $query->where('institution_requests.library_name', 'like', "%{$s}%");

        }
        $perPage = 15;
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
        $accreditation = EvaluationAssignment::query()
            ->where(['id' => $id])
            //->with('proposalState')
            //->with('accreditationProposalFiles')            
            ->get();
        //$accre_files = AccreditationProposalFiles::query();


        if (is_null($accreditation)) {
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
