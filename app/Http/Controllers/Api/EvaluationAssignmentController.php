<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluasiAssignmentResource;
use App\Models\AccreditationContent;
use App\Models\AccreditationProposal;
use App\Models\Assessor;
use App\Models\Evaluation;
use App\Models\EvaluationAssignment;
use App\Models\EvaluationContent;
use App\Models\InstrumentAspect;
use App\Models\InstrumentAspectPoint;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;

class EvaluationAssignmentController extends BaseController
{
    //
    public function destroy(EvaluationAssignment $model)
    {
        //delete files
        if (is_object($model)) {
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
        }
        $query = EvaluationAssignment::query()
            ->join('accreditation_proposals', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
            ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
            ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
            //->join('evaluation_assignments', 'evaluation_assignments.accreditation_proposal_id', '=', 'accreditation_proposals.id')
            ->select([
                'accreditation_proposals.proposal_date',
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
                'village_name as village',
                'assessor_id'
            ]);
        if ($is_assessor) {
            $assessor = Assessor::where('user_id', '=', $user_id)->first();
            $query->where('evaluation_assignments.assessor_id', '=', $assessor->id);
        }
        if ($s = $request->input(key: 'library_name')) { //filter berdasarkan name            
            $query->where('institution_requests.library_name', 'like', "%{$s}%");
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
        $perPage = $request->input(key: 'pageSize', default: 10);
        $page = $request->input(key: 'page', default: 1);
        $total = $query->count();
        $response = $query->offset(value: ($page - 1) * $perPage)
            ->limit($perPage)
            ->paginate();
        $data['evaluation_assignments'] = $response;
        $data['user_access'] = $request_header;
        return $this->sendResponse($data, "Success", $total);
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

    public function uploadInstrument(Request $request)
    {
        $input = $request->all();
        $temp_request_header = $request->header('Access-User');
        $request_header = str_replace('\"', '"', $temp_request_header);
        $request_header = json_decode($request_header, true);
        $user_id = $request_header['id'];
        $validator = Validator::make($input, [
            'file' => ['required', 'extensions:xlsx', 'max:2048'], //'required|mimes:xlsx,pdf| max:2048',
            'accreditation_proposal_id' => 'required',
            //'proposal_document_id' => 'required',
            //'instrument_component_id' => 'nullable',
            //'document_url' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        if ($request->file()) {
            //$document = ProposalDocument::find($input['proposal_document_id']);
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getMimeType(); //getClientMimeType();
            $file_path = $request->file('file')->store($input['accreditation_proposal_id']);
            $accreditation = AccreditationProposal::find($input['accreditation_proposal_id']);
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
                    $evaluation->file_name = $file_name;
                    $evaluation->file_type = $file_type;
                    $evaluation->file_path = $file_path;
                    $evaluation->update();
                } else {
                    $data = [
                        'accreditation_proposal_id' => $input['accreditation_proposal_id'],
                        'evaluation_assignment_id' => $evaluation_assignment->id,
                        'assessor_id' => $assessor->id,
                        'file_name' => $file_name,
                        'file_type' => $file_type,
                        'file_path' => $file_path,
                    ];

                    $evaluation = Evaluation::create($data);
                }
                //update assignment state
                $evaluation_assignment->assignment_state_id = 3; //selesai
                $evaluation_assignment->save();
                $accreditation = AccreditationProposal::find($input['accreditation_proposal_id']);
                $accreditation->proposal_state_id = 3;
                $accreditation->save();

                $params['file_path'] = $file_path;
                $params['accreditation_proposal_id'] = $input['accreditation_proposal_id'];
                $params['evaluation_id'] = $evaluation->id;
                $instrument_id = $accreditation->instrument_id;
                $temp_file_name = substr($file_name, 0, strlen($file_name) - 5);
                if ($temp_file_name == $instrument_id) {
                    $evaluation_contents = $this->readInstrument($params);
                    $return['evaluation_contents'] = $evaluation_contents;
                    return $this->sendResponse($return, 'Success');
                } else {
                    return $this->sendError('Wrong Instrument', "You probably uploaded a wrong instrument!");
                }
            } else {
                return $this->sendError('Not found!', "Accreditation Proposal not found, make sure you provide the ID!");
            }
            //return $this->sendResponse($request_header);
        } else {
            return $this->sendError('File Error!', $validator->errors());
        }
    }

    private function readInstrument($params)
    {
        //delete penilaian terlebih dahulu
        EvaluationContent::where('evaluation_id', '=', $params['evaluation_id'])
            ->delete();
        $file_path = Storage::disk('local')->path($params['file_path']); //base_path($params['file_path']);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $start_row = 3;
        $butir = $spreadsheet->getActiveSheet(0)->getCell('A' . $start_row)->getCalculatedValue();
        $butir = str_replace('.', '', $butir);
        //$ins_component_id = trim($spreadsheet->getActiveSheet(0)->getCell('I' . strval($start_row))->getCalculatedValue());
        
        $main_component_id = '';
        $obj_instrument = new \ArrayObject();
        while (is_numeric($butir)) {
            $butir = $spreadsheet->getActiveSheet()->getCell('A' . $start_row)->getCalculatedValue();
            $butir = str_replace('.', '', $butir);
            $value = trim($spreadsheet->getActiveSheet()->getCell('I' . strval($start_row))->getCalculatedValue());
            $comment = trim($spreadsheet->getActiveSheet()->getCell('J' . strval($start_row))->getCalculatedValue());
            $pleno = trim($spreadsheet->getActiveSheet()->getCell('K' . strval($start_row))->getCalculatedValue());
            $banding = trim($spreadsheet->getActiveSheet()->getCell('L' . strval($start_row))->getCalculatedValue());
            $ins_component_id = trim($spreadsheet->getActiveSheet()->getCell('M' . strval($start_row))->getCalculatedValue());
            
            if (!empty($ins_component_id)) {
                $aspect_id = trim($spreadsheet->getActiveSheet()->getCell('N' . strval($start_row))->getCalculatedValue());
                $instrument_component = InstrumentComponent::where('id','=',$ins_component_id)->first();
                    //->where('type', '=', 'main')->first();
                if (is_object($instrument_component)) {
                    $main_component_id = $instrument_component->id;
                } 
                $instrument_aspect = InstrumentAspect::where('id','=',$aspect_id)->first();
                $aspect = '-';
                if (is_object($instrument_aspect)) {
                    $aspect = $instrument_aspect->aspect;
                }
                $instrument_aspect_point = InstrumentAspectPoint::where('instrument_aspect_id', '=', $aspect_id)
                    ->where('value', '=', $value)->first();
                $statement = '-';
                $instrument_aspect_point_id = '';
                if (is_object($instrument_aspect_point)) {
                    $statement = $instrument_aspect_point->statement;
                    $value = $instrument_aspect_point->value;
                    $instrument_aspect_point_id = $instrument_aspect_point->id;
                } else {
                    $value = 0;
                }

                $accreditation_content = AccreditationContent::where('accreditation_proposal_id', '=', $params['accreditation_proposal_id'])
                    ->where('main_component_id', '=', $ins_component_id)
                    ->where('aspectable_id', '=', $aspect_id)
                    ->where('instrument_aspect_point_id', '=', $instrument_aspect_point_id)->first();

                if (is_object($accreditation_content)) {
                    $accre_content_id = $accreditation_content->id;
                    $evaluation_content = new EvaluationContent();
                    $evaluation_content->evaluation_id = $params['evaluation_id'];
                    $evaluation_content->accreditation_content_id = $accre_content_id;
                    $evaluation_content->main_component_id = $main_component_id;
                    $evaluation_content->instrument_aspect_point_id = $aspect_id;
                    //$evaluation_content->aspect = $aspect;
                    $evaluation_content->statement = $statement;
                    $evaluation_content->value = $value;
                    $evaluation_content->comment = $comment;
                    if (!is_numeric($pleno)) {
                        $pleno = 0;
                    }
                    if (!is_numeric($banding)) {
                        $banding = 0;
                    }
                    $evaluation_content->pleno = $pleno;
                    $evaluation_content->banding = $banding;
                    //$evaluation_content->accreditation_proposal_id = $params['accreditation_proposal_id'];
                    //$evaluation_content->butir = $butir;
                    if ($aspect_id != '') {
                        $evaluation_content->save();
                    }
                    $obj_instrument->append($evaluation_content);
                }
            }

            $start_row++;
        }
        return $obj_instrument->getArrayCopy();;
    }
}
