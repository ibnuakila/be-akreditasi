<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccreditationProposal;
use App\Models\Evaluation;
use App\Models\EvaluationAssignment;
use App\Models\EvaluationContent;
use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends BaseController implements ICrud
{
    //
    /**
     * 
     * @param $model
     */
    public function destroy($id)
    {
        $evaluasi = EvaluationAssignment::find($id);
        if (is_object($evaluasi)) {
            $ret = $evaluasi->delete();
            $this->sendResponse([], "Delete successful", $ret);
        } else {
            $this->sendError('Error', 'Delete fail');
        }
    }

    public function index()
    {

    }

    public function list(Request $request)//with filter
    {
        $is_assessor = false;
        if ($request->hasHeader('Access-User')) {
            $temp_request_header = $request->header('Access-User');
            $request_header = str_replace('\"', '"', $temp_request_header);
            $request_header = json_decode($request_header, true);
            $user_id = $request_header['id'];
            $roles = $request_header['roles'];
            foreach ($roles as $role) {
                if ($role['name'] == 'Asesor') {
                    $is_assessor = true;
                }
            }
            if ($is_assessor) {
                $query = Evaluation::query()
                    ->join('evaluation_assignments', 'evaluations.evaluation_assignment_id', '=', 'evaluation_assignments.id')
                    ->join('accreditation_proposals', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                    ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                    ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                    ->join('assessors', 'assessors.id', '=', 'evaluations.assessor_id')
                    ->join('instruments', 'accreditation_proposals.instrument_id', '=', 'instruments.id')
                    ->where('assessors.user_id', '=', $user_id)
                    ->select([
                        'accreditation_proposals.proposal_date',
                        //'evaluation_assignments.*',
                        'evaluations.*',
                        'proposal_states.state_name',
                        'instruments.category',
                        'assessors.name as assessor',
                        'library_name',
                        'npp',
                        'agency_name',
                        'institution_head_name',
                        'telephone_number',
                        'province_name as province',
                        'city_name as city',
                        'subdistrict_name as subdistrict',
                        'village_name as village'
                    ]);
            } else {
                $query = Evaluation::query()
                    ->join('evaluation_assignments', 'evaluations.evaluation_assignment_id', '=', 'evaluation_assignments.id')
                    ->join('accreditation_proposals', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                    ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                    ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                    ->join('assessors', 'assessors.id', '=', 'evaluations.assessor_id')
                    ->join('instruments', 'accreditation_proposals.instrument_id', '=', 'instruments.id')
                    ->select([
                        'accreditation_proposals.proposal_date',
                        //'evaluation_assignments.*',
                        'evaluations.*',
                        'proposal_states.state_name',
                        'instruments.category',
                        'assessors.name as assessor',
                        'library_name',
                        'npp',
                        'agency_name',
                        'institution_head_name',
                        'telephone_number',
                        'province_name as province',
                        'city_name as city',
                        'subdistrict_name as subdistrict',
                        'village_name as village'
                    ]);
            }

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
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }

    /**
     * 
     * @param $id
     */
    public function show($id)
    {
        $accre_propose = AccreditationProposal::find($id);
        $instrument = [];
        if (is_object($accre_propose)) {
            $instrument = Instrument::query()
                ->where('id', $accre_propose->instrument_id)  // Or another condition to filter the Instrument
                ->with([
                    'instrumentComponent' => function ($query) use ($id) {
                        $query->where('type', 'main')
                            ->with([
                                'children' => function ($query) use ($id) { // Load child components
                                    $query->with([
                                        'children' => function ($query) use ($id) {
                                        $query->with([
                                            'instrumentAspect' => function ($query) use ($id) { // Load aspects of each component
                                                $query->with([
                                                    'instrumentAspectPoint' => function ($query) use ($id) {
                                                    $query->with([
                                                        'accreditationContent' => function ($query) use ($id) {
                                                            $query->where('accreditation_proposal_id', $id);
                                                        }
                                                    ])
                                                        ->with([
                                                            'evaluationContent' => function ($query) use ($id) {
                                                                $query->join('evaluations', 'evaluations.id', '=', 'evaluation_contents.evaluation_id')
                                                                    ->where('evaluations.accreditation_proposal_id', '=', $id)
                                                                    ->select('evaluation_contents.*');
                                                            }
                                                        ])
                                                        ->get();
                                                }
                                                ]); // Load aspect points for each aspect
                                            },
                                        ]);
                                    },        // Recursively load more children if needed
                                        'instrumentAspect' => function ($query) { // Load aspects of each component
                                        $query->with('instrumentAspectPoint'); // Load aspect points for each aspect
                                    },
                                    ]);
                                },
                                'instrumentAspect' => function ($query) { // Load aspects of main components
                                    $query->with('instrumentAspectPoint'); // Load aspect points
                                },
                            ]);
                    },
                ])
                ->first();
        }
        return $this->sendResponse($instrument, 'Success', null);

    }

    /**
     * 
     * @param $request
     */
    public function store(Request $request)
    {

    }

    /**
     * 
     * @param $request
     * @param $model
     */
    public function update(Request $request, $id)
    {

    }

    public function updateRow(Request $request)
    {
        $input = $request->all();
        $valid = Validator::make($input, [
            'id' => 'required',
            'value' => 'nullable',
            'pleno' => 'nullable',
            'banding' => 'nullable'
        ]);
        if ($valid->fails()) {
            return $this->sendError('Error', $valid->errors());
        }
        $evaluation_content = EvaluationContent::find($input['id']);
        if (is_object($evaluation_content)) {
            if (!empty($input['value'])) {
                $evaluation_content->value = $input['value'];
            }
            if (!empty($input['pleno'])) {
                $evaluation_content->pleno = $input['pleno'];
            }
            if (!empty($input['banding'])) {
                $evaluation_content->value = $input['banding'];
            }
            $evaluation_content->update();
            return $this->sendResponse($evaluation_content, 'Success', null);
        } else {
            return $this->sendError('Error', 'Object not found');
        }
    }
}
