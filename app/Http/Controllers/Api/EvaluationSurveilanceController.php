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
                if ($role['name'] == 'Asesor') {
                    $is_assessor = true;
                }
            }
            if ($is_assessor) {
            } else {
                $evaluation_surveilance = EvaluationSurveilance::all();
            }
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
            $assessor = Assessor::where('user_id', '=', $user_id)->first();
            if (is_object($assessor)) {
                $evaluation_assignment = EvaluationAssignment::query()
                    ->where('accreditation_proposal_id', '=', $input['accreditation_proposal_id'])
                    ->where('assessor_id', '=', $assessor->id)->first();
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
                    foreach ($surveilance as $survei) {
                        $data_recom = [
                            'main_component_id' => $survei->id,
                            'nama' => $survei->name,
                            'nilai' => $survei->nilai,
                            'keterangan' => $survei->keterangan,
                            'evaluation_id' => $survei->id
                        ];
                        EvaluationSurveilance::create($data_recom);
                    }
                }
            }
            if ($validator->fails()) {
                return $this->sendError('Validation Error!', $validator->errors());
            }
            //
            return $this->sendResponse(($evaluation), 'Feedback Created', $evaluation->count());
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }

    public function update(Request $request, EvaluationSurveilance $model) {}

    public function destroy(EvaluationSurveilance $model) {}

    public function show() {}
}
