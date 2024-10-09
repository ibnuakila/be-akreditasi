<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                ->where(['user_id' => $user_id])->get();
        if(is_object($institution_request)){
            $data['institution_request'] = $institution_request;
        }
        $accreditation_proposal = AccreditationProposal::query()
                ->where(['institution_id']);
    }

    public function show($id) {
        
    }

    public function store(Request $request) {
        
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
