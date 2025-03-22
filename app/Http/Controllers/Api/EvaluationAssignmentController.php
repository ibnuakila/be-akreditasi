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
use App\Models\EvaluationContentAssessor;
use App\Models\EvaluationRecomendation;
use App\Models\InstrumentAspect;
use App\Models\InstrumentAspectPoint;
use App\Models\InstrumentComponent;
use App\Models\MergedEvaluationContent;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;

class EvaluationAssignmentController extends BaseController
{
    
    public function addNew($id, Request $request)
    {
        if ($request->hasHeader('Access-User')) {
            $accreditation_proposal = AccreditationProposal::find($id);
            if(is_object($accreditation_proposal)){
                $instrument_id = $accreditation_proposal->instrument_id;
                $instrument_component = InstrumentComponent::where('instrument_id', '=', $instrument_id)
                ->where('type', '=', 'main')->get();
                return $this->sendResponse($instrument_component, 'Success', $instrument_component->count());
            }else{
                return $this->sendError('Error', 'Object not found!');
            }
            
            
        }else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }
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
        if ($is_assessor) {
            $user_access = $request_header;
            $query = AccreditationProposal::query()
                //->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                ->join('evaluation_assignments', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                ->join('assessors', 'evaluation_assignments.assessor_id', '=', 'assessors.id')
                ->whereIn('accreditation_proposals.proposal_state_id', [2,3])
                ->Where('institution_requests.status', '=', 'valid')
                ->where('evaluation_assignments.assignment_state_id', '=', 1)
                ->where('assessors.user_id', '=', $user_id)
                ->select([
                    'accreditation_proposals.*',
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
                    'village_name as village',
                    'assessor_id',
                    'assessors.name as assessor'
                ]);
                if ($s = $request->input(key: 'search')) { //filter berdasarkan name            
                    $query->where('institution_requests.library_name', 'like', "%{$s}%")
                    ->orWhere('institution_requests.agency_name', 'like', "%{$s}%");
                }
                if ($s = $request->input(key: 'province_id')) {//filter berdasarkan name            
                    $query->where('institution_requests.province_id', '=', "{$s}");
                }
                if ($s = $request->input(key: 'city_id')) {//filter berdasarkan name            
                    $query->where('institution_requests.city_id', '=', "{$s}");
                }
                if ($s = $request->input(key: 'subdistrict_id')) {//filter berdasarkan name            
                    $query->where('institution_requests.subdistrict_id', '=', "{$s}");
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
            $total = $response->count();
        } else {

            $query = AccreditationProposal::query()
                //->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                ->join('evaluation_assignments', 'accreditation_proposals.id', '=', 'evaluation_assignments.accreditation_proposal_id')
                ->join('assessors', 'evaluation_assignments.assessor_id', '=', 'assessors.id')
                //->where('accreditation_proposals.proposal_state_id', '=', 2)
                ->Where('institution_requests.status', '=', 'valid')
                //->where('assessors.user_id', '=', $user_id)
                ->select([
                    'accreditation_proposals.*',
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
                    'village_name as village',
                    'assessor_id',
                    'assessors.name as assessor'
                ]);
            if ($is_assessor) {
                $assessor = Assessor::where('user_id', '=', $user_id)->first();
                $s = $request->input(key: 'search');
                $query->where('evaluation_assignments.assessor_id', '=', $assessor->id)
                ->orWhere('institution_requests.agency_name', 'like', "%{$s}%");
            }
            if ($s = $request->input(key: 'search')) { //filter berdasarkan name            
                $query->where('institution_requests.library_name', 'like', "%{$s}%");
            }
            if ($s = $request->input(key: 'province_id')) {//filter berdasarkan name            
                $query->where('institution_requests.province_id', '=', "{$s}");
            }
            if ($s = $request->input(key: 'city_id')) {//filter berdasarkan name            
                $query->where('institution_requests.city_id', '=', "{$s}");
            }
            if ($s = $request->input(key: 'subdistrict_id')) {//filter berdasarkan name            
                $query->where('institution_requests.subdistrict_id', '=', "{$s}");
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

        }
        //$data['evaluation_assignments'] = $response;
        //$data['user_access'] = $request_header;
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

            return $this->sendError('Assignment not found!');
        }
        return $this->sendResponse($evaluasi, 'Assignment Available', 1);
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
            $return['recommendation'] = $input['components'];
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
                //check jenis instrument 
                $file_path = Storage::disk('local')->path($params['file_path']); 
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                $params['spreadsheet'] = $spreadsheet;
                $temp_inst_name = $spreadsheet->getActiveSheet()->getCell('A1' )->getCalculatedValue();

                if ($temp_inst_name == $instrument_id) {
                    $accre_content = AccreditationContent::where('accreditation_proposal_id', $input['accreditation_proposal_id'])->get();

                    $eval_contents = new \ArrayObject();
                    //hapus data evaluation_contents
                    DB::table('evaluation_contents')->where('evaluation_id', $evaluation->id)->delete();
                    //copy data accreditation_contents ke evaluation_contents
                    foreach ($accre_content as $ac) {
                        $eval_data = [
                            'evaluation_id' => $evaluation->id,
                            'statement' => $ac->statement,
                            'value' => $ac->value,
                            'accreditation_content_id' => $ac->id,
                            'main_component_id' => $ac->main_component_id,
                            'instrument_aspect_point_id' => $ac->instrument_aspect_point_id,
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $eval_content = EvaluationContent::create($eval_data);
                        $eval_contents->append($eval_data);
                    }
                    //baca instrument
                    $evaluation_contents = $this->readInstrument($params);
                    $return['evaluation_contents'] = $evaluation_contents;
                    
                    //update skor evaluasi
                    $eval_contents = DB::table('evaluation_contents')
                        ->select(
                            'instrument_components.name',
                            'instrument_components.weight',
                            //DB::raw('SUM(accreditation_contents.value) as nilai_sa'),
                            //DB::raw('(SUM(accreditation_contents.value) * instrument_components.weight) / 100 as total_nilai_sa'),
                            DB::raw('SUM(evaluation_contents.value) as nilai_evaluasi'),
                            DB::raw('(SUM(evaluation_contents.value) / (COUNT(evaluation_contents.value) * 5)) * instrument_components.weight AS total_nilai_evaluasi'),
                            'evaluation_contents.main_component_id'
                        )
                        ->join('instrument_components', 'evaluation_contents.main_component_id', '=', 'instrument_components.id')
                        //->leftJoin('evaluation_contents', 'accreditation_contents.id', '=', 'evaluation_contents.accreditation_content_id')
                        ->where('evaluation_contents.evaluation_id', $evaluation->id)
                        ->groupBy('evaluation_contents.main_component_id', 'instrument_components.name', 'instrument_components.weight')
                        ->get();
                    $skor = 0;
                    foreach ($eval_contents as $ec) {
                        $skor = $skor + $ec->total_nilai_evaluasi;
                    }
                    $evaluation->skor = $skor;
                    $evaluation->update();

                    //recommendation component
                    $recommendation = json_decode($input['component']);
                    foreach ($recommendation->data as $recom) {
                        $data_recom = [
                            'main_component_id' => $recom->id,
                            'nama' => $recom->name,
                            'content' => $recom->content,
                            'evaluation_id' => $evaluation->id
                        ];
                        EvaluationRecomendation::create($data_recom);
                    }

                    $evaluations = Evaluation::where('accreditation_proposal_id', $input['accreditation_proposal_id'])->get();
                    $params['accreditation_proposal_id'] = $input['accreditation_proposal_id'];
                    $params['accre_content'] = $accre_content;
                    if(count($evaluations) > 1){
                        $this->mergedEvaluation($params);
                    }
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
        //EvaluationContent::where('evaluation_id', '=', $params['evaluation_id'])
        //->delete();
        //$file_path = Storage::disk('local')->path($params['file_path']); //base_path($params['file_path']);
        $spreadsheet = $params['spreadsheet'];//\PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $start_row = 3;
        $butir = trim($spreadsheet->getActiveSheet()->getCell('A' . strval($start_row))->getCalculatedValue());
        if (strpos($butir, '.') !== false) {
            $butir = (int)str_replace('.', '', $butir);
        }
        //$ins_component_id = trim($spreadsheet->getActiveSheet(0)->getCell('I' . strval($start_row))->getCalculatedValue());

        $main_component_id = '';
        $obj_instrument = new \ArrayObject();
        while (is_numeric($butir)) {
            $butir = trim($spreadsheet->getActiveSheet()->getCell('A' . $start_row)->getCalculatedValue());
            $butir = str_replace('.', '', $butir);
            $nilaisa = trim($spreadsheet->getActiveSheet()->getCell('H' . strval($start_row))->getCalculatedValue());        
            $nilai = trim($spreadsheet->getActiveSheet()->getCell('I' . strval($start_row))->getCalculatedValue());            
            
            $comment = trim($spreadsheet->getActiveSheet()->getCell('J' . strval($start_row))->getCalculatedValue());
            $pleno = trim($spreadsheet->getActiveSheet()->getCell('K' . strval($start_row))->getCalculatedValue());
            $banding = trim($spreadsheet->getActiveSheet()->getCell('L' . strval($start_row))->getCalculatedValue());
            $ins_component_id = trim($spreadsheet->getActiveSheet()->getCell('M' . strval($start_row))->getCalculatedValue());
            $aspect_id = trim($spreadsheet->getActiveSheet()->getCell('N' . strval($start_row))->getCalculatedValue());
            $data = [
                'butir' => $butir,
                'nilai' => $nilai,
                'comment' => $comment,
                'pleno' => $pleno,
                'banding' => $banding,
                'ins_component_id' => $ins_component_id,
                'aspect_id' => $aspect_id
            ];

            //cari instrument_aspect_points berdasarkan nilai yg lama
            if($nilaisa !== ''){
                $old_instrument_aspect_point = InstrumentAspectPoint::where('instrument_aspect_id', '=', $aspect_id)
                ->where('value', '=', $nilaisa)->first();
                if(is_object($old_instrument_aspect_point)){
                    $old_instrument_aspect_point_id = $old_instrument_aspect_point->id;
                }
            }else{
                $old_instrument_aspect_point_id = null;
            }
            //cari instrument_aspect_points berdasarkan nilai yg baru
            if($nilai !== ''){
                $instrument_aspect_point = InstrumentAspectPoint::where('instrument_aspect_id', '=', $aspect_id)
                ->where('value', '=', $nilai)->first();
            }else{                
                $instrument_aspect_point = null;
            }
            $statement = '-';
            $instrument_aspect_point_id = '';

            if (is_object($instrument_aspect_point)) {
                $statement = $instrument_aspect_point->statement;
                $nilai = $instrument_aspect_point->value;
                $instrument_aspect_point_id = $instrument_aspect_point->id;
            }

            //if($instrument_aspect_point_id !== ''){
                $evaluation_content = EvaluationContent::where('evaluation_id', '=', $params['evaluation_id'])
                ->where('instrument_aspect_point_id', '=', $old_instrument_aspect_point_id)->first();
            //}else{
            //    $evaluation_content = null;
            //}
            if (is_object($evaluation_content)) {
                
                if (!is_numeric($pleno)) {
                    $pleno = 0;
                }
                if (!is_numeric($banding)) {
                    $banding = 0;
                }
                if($nilai !== ''){
                    $evaluation_content->update([
                        'instrument_aspect_point_id' => $instrument_aspect_point_id,
                        'statement' => $statement,
                        'value' => $nilai,
                        'comment' => $comment,
                        'pleno' => $pleno,
                        'banding' => $banding,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }else{
                    $evaluation_content->delete();
                }
                $obj_instrument->append($evaluation_content);
            }

            $start_row++;
        }
        return $obj_instrument->getArrayCopy();

    }

    private function mergedEvaluation($params)
    {
        if(is_array(($params))){
            MergedEvaluationContent::query()
            ->where('accreditation_proposal_id', '=', $params['accreditation_proposal_id'])
            ->delete();
            foreach ($params['accre_content'] as $evaluation){
                $evaluations = EvaluationContent::where('accreditation_content_id', '=', $evaluation->id)->get();
                
                $statement = '';
                $value = 0;
                $evaluation_id = '';
                $instrument_aspect_point_id = '';
                $comment = '';
                if(count($evaluations) > 0){
                    
                    foreach ($evaluations as $eval){
                        if($eval->value != 0){
                            $comment = $eval->comment;
                            $evaluation_id = $eval->evaluation_id;
                            $instrument_aspect_point = InstrumentAspectPoint::where('instrument_aspect_id', '=', $evaluation->aspectable_id) //cari berdasarkan aspectable_id
                                ->where('value', '=', $eval->value)->first();
                            if(is_object($instrument_aspect_point)){
                                $statement = $instrument_aspect_point->statement;   
                                $instrument_aspect_point_id = $instrument_aspect_point->id;   
                                $value = $instrument_aspect_point->value;                          
                            }
                        }
                        
                    }
                    
                }
                $eval_data = [
                    'evaluation_id' => $evaluation_id,
                    'statement' => $statement,
                    'value' => $value,
                    'comment' => $comment,
                    'pleno' => $value,
                    'accreditation_content_id' => $evaluation->id,
                    'main_component_id' => $evaluation->main_component_id,
                    'instrument_aspect_point_id' => $instrument_aspect_point_id,
                    'accreditation_proposal_id' => $evaluation->accreditation_proposal_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $merged_evaluation_content = MergedEvaluationContent::create($eval_data);                        
                
            }
        }
    }
}
