<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvaluationSurveilance;
use App\Models\AccreditationProposal;
use App\Models\Assessor;
use App\Models\Evaluation;
use App\Models\EvaluationAssignment;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationSurveilanceController extends BaseController
{
    //
    public function addNew($id, Request $request)
    {
        if ($request->hasHeader('Access-User')) {
            $accreditation_proposal = AccreditationProposal::find($id);
            if (is_object($accreditation_proposal)) {
                $instrument_id = $accreditation_proposal->instrument_id;
                $instrument_component = InstrumentComponent::where('instrument_id', '=', $instrument_id)
                    ->where('type', '=', 'main')->get();
                $data['accreditation_proposal_id'] = $id;
                $data['instrument_component'] = $instrument_component;
                $penilaian = [
                    'Menurun' => 'Menurun',
                    'Tetap' => 'Tetap',
                    'Meningkat' => 'Meningkat'
                ];
                $data['penilaian'] = $penilaian;
                return $this->sendResponse($data, 'Success', 3);
            } else {
                return $this->sendError('Error', 'Object not found!');
            }
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }

    public function list(Request $request) //with filter
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
                        'evaluation_assignments.method',
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
                        'evaluation_assignments.method',
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

            if ($s = $request->input(key: 'search')) { //filter berdasarkan name            
                $query->where('institution_requests.library_name', 'like', "%{$s}%");
            }
            if ($s = $request->input(key: 'province_id')) { //filter berdasarkan name            
                $query->where('institution_requests.province_id', '=', "{$s}");
            }
            if ($s = $request->input(key: 'city_id')) { //filter berdasarkan name            
                $query->where('institution_requests.city_id', '=', "{$s}");
            }
            if ($s = $request->input(key: 'subdistrict_id')) { //filter berdasarkan name            
                $query->where('institution_requests.subdistrict_id', '=', "{$s}");
            }
            if ($s = $request->input(key: 'state_name')) { //filter berdasarkan name            
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

    public function store(Request $request)
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
            $input = $request->all();


            //validating---------------------------
            $validator = Validator::make($input, [
                'accreditation_proposal_id' => 'required',
                'surveilance' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error!', $validator->errors());
            }

            $assessor = Assessor::where('user_id', '=', $user_id)->first();
            if (is_object($assessor)) {
                $evaluation_assignment = EvaluationAssignment::query()
                    ->where('accreditation_proposal_id', '=', $input['accreditation_proposal_id'])
                    ->where('assessor_id', '=', $assessor->id)
                    ->where('method', '=', 'Onsite')
                    ->first();
            } else {
                $evaluation_assignment = null;
            }
            if (is_object($evaluation_assignment)) {
                $evaluation = Evaluation::where('evaluation_assignment_id', '=', $evaluation_assignment->id)->first();
                if (is_object($evaluation)) {
                    // $evaluation->file_name = $file_name;
                    // $evaluation->file_type = $file_type;
                    // $evaluation->file_path = $file_path;
                    $evaluation->update();
                } else {
                    $data = [
                        'accreditation_proposal_id' => $input['accreditation_proposal_id'],
                        'evaluation_assignment_id' => $evaluation_assignment->id,
                        'assessor_id' => $assessor->id
                        // 'file_name' => $file_name,
                        // 'file_type' => $file_type,
                        // 'file_path' => $file_path,
                    ];

                    $evaluation = Evaluation::create($data);
                }
                //update assignment state
                $evaluation_assignment->assignment_state_id = 3; //selesai
                $evaluation_assignment->save();
                $surveilance = json_decode($input['surveilance']);
                if (is_object($evaluation)) {
                    foreach ($surveilance->data as $survei) {
                        $data_recom = [
                            'main_component_id' => $survei->main_component_id,
                            'nama' => $survei->nama,
                            'nilai' => $survei->nilai,
                            'keterangan' => $survei->keterangan,
                            'evaluation_id' => $evaluation->id
                        ];
                        EvaluationSurveilance::create($data_recom);
                    }
                }
                return $this->sendResponse(($evaluation), 'Feedback Created', $evaluation->count());
            }else{
                return $this->sendResponse(($evaluation_assignment), 'Assignment Not Found!', 0);
            }
            
            //return $this->sendResponse(($evaluation), 'Feedback Created', $evaluation->count());
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }

    public function update(Request $request, EvaluationSurveilance $model) {}

    public function destroy(EvaluationSurveilance $model) {}

    public function show($id,Request $request) {
        if ($request->hasHeader('Access-User')) {
            
            $evaluation = Evaluation::where('id', '=', $id)->first();
            if (is_object($evaluation)) {
                $accreditation_proposal = AccreditationProposal::where('id', '=', $evaluation->accreditation_proposal_id)->first();
                $evaluation_surveilance = EvaluationSurveilance::where('evaluation_id','=',$id)->get();
                $instrument_id = $accreditation_proposal->instrument_id;
                $instrument_component = InstrumentComponent::where('instrument_id', '=', $instrument_id)
                    ->where('type', '=', 'main')->get();
                $data['accreditation_proposal_id'] = $id;
                $data['evaluation_surveilance'] = $evaluation_surveilance;
                $penilaian = [
                    'Menurun' => 'Menurun',
                    'Tetap' => 'Tetap',
                    'Meningkat' => 'Meningkat'
                ];
                $data['penilaian'] = $penilaian;
                return $this->sendResponse($data, 'Success', 3);
            } else {
                return $this->sendError('Error', 'Object not found!');
            }
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }
}
