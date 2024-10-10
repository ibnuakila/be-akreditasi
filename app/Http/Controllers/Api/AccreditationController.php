<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccreditationProposalResource;
use App\Models\InstitutionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;
use App\Models\AccreditationProposal;
use App\Models\ProposalState;
use App\Models\AccreditationProposalFiles;
use App\Models\ProposalDocument;
use App\Models\Instrument;
use App\Models\Province;
use App\Models\Region;
use App\Models\ProvinceRegion;

class AccreditationController extends BaseController //implements ICrud
{
    //
    public function destroy($model) {
        
    }

    public function index($user_id) {
        $institution_request = \App\Models\InstitutionRequest::query()
                ->where(['user_id' => $user_id])->first();
        if(is_object($institution_request)){
            $data['institution_request'] = $institution_request;
        }
        $accreditation_proposal = AccreditationProposal::query()
                ->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.institution_id', '=', 'institution_requests.institution_id')
                ->where(['accreditation_proposals.institution_id' => $institution_request->institution_id])
                ->with('proposalState')
                ->first();
        if(is_object($accreditation_proposal)){
            $data['accreditation_proposal'] = $accreditation_proposal;
        }
        return $this->sendResponse($data, "Success",0);
    }

    public function show($id) {
        
    }

    public function store(Request $request) {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            //institution-request
            'category' => $input['category'],
            'region_id' => $input['region_id'],
            'library_name' => 'required',
            'npp' => 'nullable',
            'agency_name' => 'required',
            'address' => 'required',
            'city_id' => 'required',
            'subdistrict_id' => 'required',
            'village_id' => 'required',
            'institution_head_name' => 'required',
            'email' => 'required',
            'telephone_number' => 'required',
            'mobile_number' => 'required',
            'library_head_name' => 'required',
            'library_worker_name' => 'nullable',
            'registration_form_file' => 'required',
            'title_count' => 'required',
            'user_id' => 'required',
            'status' => 'nullable',
            'last_predicate' => 'nullable',
            'last_certification_date' => 'nullable',
            'type' => 'required',
            'accreditation_proposal_id' => 'nullable',
            'validated_at' => 'nullable',
            'institution_id' => 'nullable',

            //accreditation-proposal
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
            'recommendation_file' => 'nullable',
            'is_valid' => 'nullable',
            'instrument_id' => 'required',
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        
        $accreditation_proposal = [
            'institution_id' => $input['institution_id'],
            'proposal_date' => $input['proposal_date'],
            'proposal_state_id' => $input['proposal_state_id'],
            'finish_date' => $input['finish_date'],
            'type' => $input['type'],
            'notes' => '',
            'accredited_at' => '',
            'predicate' => '',
            'certificate_status' => '',
            'certificate_expires_at' => '',
            'pleno_date' => '',
            'certificate_file' => '',
            'recommendation_file' => '',
            'is_valid' => '',
            'instrument_id' => $input['instrument_id'],
            'category' => $input['category']
        ];
        $proposal = AccreditationProposal::create($accreditation_proposal);

        $institution_request = [
            'category' => $input['category'],
            'region_id' => $input['region_id'],
            'library_name' => $input['library_name'],
            'npp' => $input['npp'],
            'agency_name' => $input['agency_name'],
            'address' => $input['address'],
            'city_id' => $input['city_id'],
            'subdistrict_id' => $input['subdistrict_id'],
            'village_id' => $input['village_id'],
            'institution_head_name' => $input['institution_head_name'],
            'email' => $input['email'],
            'telephone_number' => $input['telephone_number'],
            'mobile_number' => $input['mobile_number'],
            'library_head_name' => $input['library_head_name'],
            'library_worker_name' => $input['library_worker_name'],
            'registration_form_file' => $input['registration_form_file'],
            'title_count' => $input['title_count'],
            'user_id' => $input['user_id'],
            'status' => $input['status'],
            'last_predicate' => $input['last_predicate'],
            'last_certification_date' => $input['last_certification_date'],
            'type' => $input['type'],
            'accreditation_proposal_id' => $proposal->id,
            'validated_at' => '',
            'institution_id' => '',
        ];
        $request = InstitutionRequest::create($institution_request);

        return $this->sendResponse(new AccreditationProposalResource($request), 'Proposal Created', $proposal->count);
    }
    
    public function storeFiles(Request $request)
    {        
        $input = $request->all();
        $validator = Validator::make($input, [
            'file' => ['required', 'extensions:pdf,xlsx', 'max:2048'], //'required|mimes:xlsx,pdf| max:2048',
            'accreditation_proposal_id' => 'required',
            'proposal_document_id' => 'required',
            'instrument_component_id' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        if($request->file()){
            $document = ProposalDocument::find($input['proposal_document_id']);
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getMimeType(); //getClientMimeType();
            $file_path = $request->file('file')->store($request->accreditation_proposal_id);
            $accre_files = AccreditationProposalFiles::query()
                ->where('accreditation_proposal_id', '=', $request->accreditation_proposal_id)
                ->where('proposal_document_id', '=', $request->proposal_document_id)->first();
            
            $return['document'] = $document;
            if(is_object($accre_files)){
                $accre_files->accreditation_proposal_id = $request->accreditation_proposal_id;
                $accre_files->proposal_document_id = $request->proposal_document_id;
                $accre_files->instrument_component_id = $document->instrument_component_id;
                $accre_files->aspect = $document->document_name;
                $accre_files->file_name = $file_name;
                $accre_files->file_type = $file_type;
                $accre_files->file_path = $file_path;
                $accre_files->update();
            }else{
                $data = [
                    'accreditation_proposal_id' => $request->accreditation_proposal_id,
                    'proposal_document_id' => $request->proposal_document_id,
                    'instrument_component_id' => $document->instrument_document_id,
                    'aspect' => $document->document_name,
                    'file_name' => $file_name,
                    'file_type' => $file_type,
                    'file_path' => $file_path
                ];
                
                $accre_files = AccreditationProposalFiles::create($data);                                
            }
            
            if(is_object($document)){
                if(trim($document->document_name) == 'Instrument Penilaian'){
                    $params['file_path'] = $accre_files->file_path;
                    $accre_contents = $this->readInstrument($params);
                    //$return['accre_contents'] = $accre_contents;
                }
            }
            $return['accre_files'] = $accre_files;
            if(isset($accre_contents)){
                $return['accre_contents'] = $accre_contents;
            }
        }else{
            return $this->sendError('File Error!', $validator->errors());        
        }
        return $this->sendResponse($return, 'Success', $accre_files->count());
    }

    public function update(Request $request, $model) {
        
    }
    
    private function readInstrument($params)
    {
        $file_path = Storage::disk('local')->path($params['file_path']); //base_path($params['file_path']);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $start_row = 6;
        $butir = $spreadsheet->getActiveSheet(0)->getCell('A' . $start_row)->getCalculatedValue();
        $butir = str_replace('.', '', $butir);
        $obj_instrument = new ArrayObject();
        while(is_numeric($butir)){
            $butir = $spreadsheet->getActiveSheet(0)->getCell('A' . $start_row)->getCalculatedValue();
            $butir = str_replace('.', '', $butir);
            $value = $spreadsheet->getActiveSheet()->getCell('H' . strval($start_row))->getCalculatedValue();
            $ins_component_id = $spreadsheet->getActiveSheet(0)->getCell('I' . strval($start_row))->getCalculatedValue();
            $aspect_id = $spreadsheet->getActiveSheet(0)->getCell('J' . strval($start_row))->getCalculatedValue();
            
            $accre_content = new \App\Models\AccreditationContent();
            $accre_content->aspectable_id = $ins_component_id;
            $accre_content->main_component_id = '';
            $accre_content->instrument_aspect_point_id = $aspect_id;
            $accre_content->aspect = '';
            $accre_content->statement = '';
            $accre_content->value = $value;
            $obj_instrument->append($accre_content);
            $start_row++;
        }
        return $obj_instrument;
    }
}
