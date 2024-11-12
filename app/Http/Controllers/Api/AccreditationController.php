<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccreditationProposalResource;
use App\Models\AccreditationContent;
use App\Models\InstitutionRequest;
use App\Models\InstrumentAspect;
use App\Models\InstrumentAspectPoint;
use App\Models\InstrumentComponent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Storage;
use App\Models\AccreditationProposal;
use App\Models\ProposalState;
use App\Models\AccreditationProposalFiles;
use App\Models\ProposalDocument;
use App\Models\Instrument;
use App\Models\Province;
use App\Models\Region;
use App\Models\ProvinceRegion;
use App\Models\City;
use App\Models\Subdistrict;
use App\Models\Village;



class AccreditationController extends BaseController //implements ICrud
{
    //
    public function destroy($id)
    {
        $insti_request = InstitutionRequest::where(['accreditation_proposal_id' => $id])->first();
        if (is_object($insti_request)) {
            $insti_request->delete();
        }
        $accre_files = AccreditationProposalFiles::where(['accreditation_proposal_id' => $id])->get();
        if ($accre_files->count() > 0) {
            foreach ($accre_files as $file) {
                $file->delete();
            }
        }
        $accre_content = AccreditationContent::where(['accreditation_proposal_id' => $id])->get();
        if ($accre_content->count() > 0) {
            foreach ($accre_content as $row) {
                $row->delete();
            }
        }
        $accre_proposal = AccreditationProposal::find($id);
        if (is_object($accre_proposal)) {
            $ret = $accre_proposal->delete();
            return $this->sendResponse([], 'Delete successful', $ret);
        } else {
            return $this->sendError('Error', 'Object not found!');
        }
    }

    public function index($user_id)
    {
        $institution_request = InstitutionRequest::query()
            ->where(['user_id' => $user_id])->get();
        if (is_object($institution_request)) {
            $data['institution_request'] = $institution_request;
        }
        $accreditation_proposal = null;
        if (is_object($institution_request)) {
            $accreditation_proposal = AccreditationProposal::query()
                ->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.institution_id', '=', 'institution_requests.institution_id')
                ->where(['institution_requests.user_id' => $user_id])
                ->with('proposalState')->get();

        }
        if (is_object($accreditation_proposal)) {
            $data['accreditation_proposal'] = $accreditation_proposal;
        }
        $instruments = Instrument::all();
        $data['instruments'] = $instruments;
        return $this->sendResponse($data, "Success", 0);
    }

    public function show($id)
    {
        $institution_request = InstitutionRequest::query()
            ->where(['accreditation_proposal_id' => $id])->first();
        if (is_object($institution_request)) {
            $data['institution_request'] = $institution_request;
        }
        $accreditation_proposal = null;
        if (is_object($institution_request)) {
            $accreditation_proposal = AccreditationProposal::query()
                ->select('accreditation_proposals.*')
                ->join('institution_requests', 'accreditation_proposals.institution_id', '=', 'institution_requests.institution_id')
                ->where(['accreditation_proposals.id' => $id])
                ->with('proposalState')
                ->first();
        }
        if (is_object($accreditation_proposal)) {
            $data['accreditation_proposal'] = $accreditation_proposal;
        }
        $instruments = Instrument::all();
        $data['instruments'] = $instruments;
        return $this->sendResponse($data, "Success", 0);
    }

    public function addNew($user_id)
    {
        /*$provinces = Province::all();
        $cities = City::first();
        $subdistricts = Subdistrict::first();
        $villages = Village::first();*/
        $Y = date('Y');
        $proposal = AccreditationProposal::where('user_id', '=', $user_id)
            ->whereBetween('proposal_date', [$Y . '-01-01', $Y . '-12-31'])
            ->first();
        if (is_object($proposal)) {
            $data['accreditation_proposal'] = $proposal;
            $message = 'Anda masih memiliki usulan akreditasi pada tahun yang sama!';
            return $this->sendError($data, $message, 500);
        } else {
            $region = Region::all();
            $category = Instrument::all();
            $type = ['baru' => 'Baru', 'reakreditasi' => 'Reakreditasi'];
            $data['region'] = $region;
            $data['category'] = $category;
            $data['type'] = $type;
            return $this->sendResponse($data, "Success", 0);
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            //institution-request
            'category' => 'required',
            'region_id' => 'required',
            'library_name' => 'required',
            'npp' => 'nullable',
            'agency_name' => 'required',
            'address' => 'required',
            'province_id' => 'required',
            'province_name' => 'required',
            'city_id' => 'required',
            'city_name' => 'required',
            'subdistrict_id' => 'required',
            'subdistrict_name' => 'required',
            'village_id' => 'required',
            'village_name' => 'required',
            'institution_head_name' => 'required',
            'email' => 'required',
            'telephone_number' => 'required',
            'mobile_number' => 'required',
            'library_head_name' => 'required',
            'library_worker_name' => 'nullable',
            'registration_form_file' => 'nullable',
            'title_count' => 'required',
            'user_id' => 'required',
            'status' => 'nullable',
            'last_predicate' => 'nullable',
            'last_certification_date' => 'nullable',
            'type' => 'required',
            'accreditation_proposal_id' => 'nullable',
            'validated_at' => 'nullable',
            'institution_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $instrument =
            $accreditation_proposal = [
                'institution_id' => $input['institution_id'],
                'proposal_date' => date('Y-m-d'),
                'proposal_state_id' => 0,
                'finish_date' => date('Y-m-d'),
                'type' => $input['type'],
                'periode' => date('Y'),
                'notes' => '',
                //'accredited_at' => '',
                'predicate' => '',
                'certificate_status' => '',
                //'certificate_expires_at' => '',
                //'pleno_date' => '',
                //'certificate_file' => '',
                //'recommendation_file' => '',
                'is_valid' => 'tidak_valid',
                'instrument_id' => $input['category'],
                'category' => $input['category'],
                'user_id' => $input['user_id']
            ];
        //sebelum create cek dulu apakah sudah ada usulan berdasarkan user_di pada tahun yg sama
        $Y = date('Y');
        $proposal = AccreditationProposal::where('user_id', '=', $input['user_id'])
            ->whereBetween('proposal_date', [$Y . '-01-01', $Y . '-12-31'])
            ->first();
        if (is_object($proposal)) {
            $proposal->update($accreditation_proposal);
        } else {
            $proposal = AccreditationProposal::create($accreditation_proposal);
        }

        $data['accreditation_proposal'] = $proposal;
        $file_path = '';
        if ($request->file()) {
            $file_path = $request->file('registration_form_file')->store('institutions/forms/');
        } /*else {
           $file_path = $request->file('registration_form_file')->store($input['user_id']);
       }*/

        $institution_request = [
            'category' => $input['category'],
            'region_id' => $input['region_id'],
            'library_name' => $input['library_name'],
            'npp' => $input['npp'],
            'agency_name' => $input['agency_name'],
            'address' => $input['address'],
            'province_id' => $input['province_id'],
            'province_name' => $input['province_name'],
            'city_id' => $input['city_id'],
            'city_name' => $input['city_name'],
            'subdistrict_id' => $input['subdistrict_id'],
            'subdistrict_name' => $input['subdistrict_name'],
            'village_id' => $input['village_id'],
            'village_name' => $input['village_name'],
            'institution_head_name' => $input['institution_head_name'],
            'email' => $input['email'],
            'telephone_number' => $input['telephone_number'],
            'mobile_number' => $input['mobile_number'],
            'library_head_name' => $input['library_head_name'],
            'library_worker_name' => $input['library_worker_name'],
            'registration_form_file' => $file_path,
            'title_count' => $input['title_count'],
            'user_id' => $input['user_id'],
            'status' => 'tidak_valid',
            'last_predicate' => $input['last_predicate'],
            'type' => $input['type'],
            'accreditation_proposal_id' => $proposal->id,
            'validated_at' => '',
            'institution_id' => $input['institution_id'],
        ];
        //$temp_inrequest = InstitutionRequest::query()
        //    ->where('user_id', '=', $input['user_id'])->first();
        //if (is_object($temp_inrequest)) {
        //    $temp_inrequest->save($institution_request);
        //    $data['institution_request'] = $temp_inrequest;
        //} else {
        $temp_inrequest = InstitutionRequest::create($institution_request);
        $data['institution_request'] = $temp_inrequest;
        //}


        $proposal_document = ProposalDocument::query()->where('instrument_id', '=', $input['category'])->get();
        $data['proposal_document'] = $proposal_document;

        return $this->sendResponse($data, 'Proposal Created', $proposal->count);
    }

    public function storeFiles(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'file' => ['required', 'extensions:pdf,xlsx', 'max:2048'], //'required|mimes:xlsx,pdf| max:2048',
            'accreditation_proposal_id' => 'required',
            'proposal_document_id' => 'required',
            'instrument_component_id' => 'nullable',
            'document_url' => 'nullable'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        if ($request->file()) {
            $document = ProposalDocument::find($input['proposal_document_id']);
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getMimeType(); //getClientOriginalExtension() //; //getClientMimeType();
            $file_path = $request->file('file')->store($input['accreditation_proposal_id']);
            $accreditation = AccreditationProposal::find($input['accreditation_proposal_id']);
            if (is_object($accreditation)) {

                $accre_files = AccreditationProposalFiles::query()
                    ->where('accreditation_proposal_id', '=', $input['accreditation_proposal_id'])
                    ->where('proposal_document_id', '=', $input['proposal_document_id'])->first();


                if (is_object($accre_files)) {
                    $accre_files->accreditation_proposal_id = $input['accreditation_proposal_id'];
                    $accre_files->proposal_document_id = $input['proposal_document_id'];
                    $accre_files->instrument_component_id = $document->instrument_component_id;
                    $accre_files->aspect = $document->document_name;
                    $accre_files->file_name = $file_name;
                    $accre_files->file_type = $file_type;
                    $accre_files->file_path = $file_path;
                    //$accre_files->document_url = $input['document_url'];
                    $accre_files->update();
                } else {
                    $data = [
                        'accreditation_proposal_id' => $input['accreditation_proposal_id'],
                        'proposal_document_id' => $input['proposal_document_id'],
                        'instrument_component_id' => $document->instrument_document_id,
                        'aspect' => $document->document_name,
                        'file_name' => $file_name,
                        'file_type' => $file_type,
                        'file_path' => $file_path,
                        //'document_url' => $input['document_url']
                    ];

                    $accre_files = AccreditationProposalFiles::create($data);
                }
                $accreditation_proposal_files = AccreditationProposalFiles::where('accreditation_proposal_id', '=', $input['accreditation_proposal_id'])
                    ->with('proposalDocument')
                    ->get();
                $return['accreditation_files'] = $accreditation_proposal_files;
                if (is_object($document)) {
                    if (trim($document->document_name) == 'Instrument Penilaian') {
                        $params['file_path'] = $accre_files->file_path;
                        $params['accreditation_proposal_id'] = $input['accreditation_proposal_id'];
                        $instrument_id = $accreditation->instrument_id;
                        $temp_file_name = substr($file_name, 0, strlen($file_name) - 5);
                        if ($temp_file_name == $instrument_id) {
                            $accre_contents = $this->readInstrument($params);
                            $return['accreditation_contents'] = $accre_contents;
                        } else {
                            return $this->sendError('Wrong Instrument', "You probably uploaded a wrong instrument!");
                        }
                    }
                    //$return['accre_files'] = $accre_files;
                    if (isset($accre_contents)) {
                        $return['accreditation_contents'] = $accre_contents;
                    }
                }
            } else {
                return $this->sendError('Not found!', "Accreditation Proposal not found, make sure you provide the ID!");
            }
        } else {
            return $this->sendError('File Error!', $validator->errors());
        }
        return $this->sendResponse($return, 'Success', $accre_files->count());
    }

    public function edit($id)
    {
        $accreditation_proposal = AccreditationProposal::find($id);
        $institution_request = InstitutionRequest::query()
            ->where('accreditation_proposal_id', '=', $id)->first();
        $accreditation_files = AccreditationProposalFiles::query()
            ->where('accreditation_proposal_id', '=', $id)
            ->with('proposalDocument')
            ->get();
        //ambil hanya dokumen yg belum diupload
        //$proposal_document = ProposalDocument::query()
        //    ->where('instrument_id', '=', $accreditation_proposal->instrument_id)->get();
        $proposal_document = ProposalDocument::join(
            'instruments',
            'proposal_documents.instrument_id',
            '=',
            'instruments.id'
        )
            ->where('instrument_id', '=', $accreditation_proposal->instrument_id)
            ->whereNotIn('proposal_documents.id', function ($query) use ($id) {
                $query->select('proposal_document_id')
                    ->from('accreditation_proposal_files')
                    ->where('accreditation_proposal_id', $id);
            })
            ->select(['proposal_documents.*'])
            ->get();
        $accreditation_contents = AccreditationContent::query()
            ->where('accreditation_proposal_id', '=', $accreditation_proposal->id)->get();
        /*$provinces = Province::all();
        $cities = City::first();
        $subdistricts = Subdistrict::first();
        $villages = Village::first();*/

        $region = Region::all();
        $category = Instrument::all();

        $type = ['baru' => 'Baru', 'reakreditasi' => 'Reakreditasi'];
        $data['accreditation_proposal'] = $accreditation_proposal;
        $data['institution_request'] = $institution_request;
        $data['accreditation_files'] = $accreditation_files;
        $data['proposal_document'] = $proposal_document;
        $data['accreditation_contents'] = $accreditation_contents;
        //$data['provinces'] = $provinces;
        //$data['cities'] = $cities;
        //$data['subdistricts'] = $subdistricts;
        //$data['villages'] = $villages;
        $data['region'] = $region;
        $data['category'] = $category;
        $data['type'] = $type;
        return $this->sendResponse($data, "Success", 0);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            //institution-request
            'category' => 'required',
            'region_id' => 'required',
            'library_name' => 'required',
            'npp' => 'nullable',
            'agency_name' => 'required',
            'address' => 'required',
            'province_id' => 'required',
            'province_name' => 'required',
            'city_id' => 'required',
            'city_name' => 'required',
            'subdistrict_id' => 'required',
            'subdistrict_name' => 'required',
            'village_id' => 'required',
            'village_name' => 'required',
            'institution_head_name' => 'required',
            'email' => 'required',
            'telephone_number' => 'required',
            'mobile_number' => 'required',
            'library_head_name' => 'required',
            'library_worker_name' => 'nullable',
            'registration_form_file' => 'nullable',
            'title_count' => 'required',
            'user_id' => 'required',
            'status' => 'nullable',
            'last_predicate' => 'nullable',
            'last_certification_date' => 'nullable',
            'type' => 'required',
            'accreditation_proposal_id' => 'nullable',
            'validated_at' => 'nullable',
            'institution_id' => 'required',


        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }

        $proposal = AccreditationProposal::find($id);
        if (is_object($proposal)) {
            $proposal->institution_id = $input['institution_id'];
            //$proposal->proposal_date = date('Y-m-d');
            $proposal->finish_date = date('Y-m-d');
            $proposal->type = $input['type'];
            $proposal->instrument_id = $input['category'];
            $proposal->category = $input['category'];
            if ($input['status'] == 'valid') {
                $proposal->proposal_state_id = 1;
            }
            $proposal->save();
        }
        $file_path = '';
        if ($request->file()) {
            $file_path = $request->file('registration_form_file')->store($proposal->id);
        } /*else {
           $file_path = $request->file('registration_form_file')->store($input['user_id']);
       }*/

        $request = InstitutionRequest::query()
            ->where('accreditation_proposal_id', '=', $id)
            ->first();
        if (is_object($request)) {
            $request->category = $input['category'];
            $request->region_id = $input['region_id'];
            $request->library_name = $input['library_name'];
            $request->npp = $input['npp'];
            $request->agency_name = $input['agency_name'];
            $request->address = $input['address'];
            $request->province_id = $input['province_id'];
            $request->province_name = $input['province_name'];
            $request->city_id = $input['city_id'];
            $request->city_name = $input['city_name'];
            $request->subdistrict_id = $input['subdistrict_id'];
            $request->subdistrict_name = $input['subdistrict_name'];
            $request->village_id = $input['village_id'];
            $request->village_name = $input['village_name'];
            $request->institution_head_name = $input['institution_head_name'];
            $request->email = $input['email'];
            $request->telephone_number = $input['telephone_number'];
            $request->mobile_number = $input['mobile_number'];
            $request->library_head_name = $input['library_head_name'];
            $request->library_worker_name = $input['library_worker_name'];
            $request->registration_form_file = $file_path;
            $request->title_count = $input['title_count'];
            $request->user_id = $input['user_id'];
            $request->status = $input['status'];
            $request->last_predicate = $input['last_predicate'];
            $request->last_certification_date = $input['last_certification_date'];
            //$request->type = $input['type'];
            $request->accreditation_proposal_id = $proposal->id;
            //$request->validated_at = '';
            $request->institution_id = $input['institution_id'];
            $request->save();
        }
        return $this->sendResponse(new AccreditationProposalResource($request), 'Proposal Updated', $proposal->count);
    }

    private function readInstrument($params)
    {
        //delete penilaian terlebih dahulu
        AccreditationContent::where('accreditation_proposal_id', '=', $params['accreditation_proposal_id'])
            ->delete();
        $file_path = Storage::disk('local')->path($params['file_path']); //base_path($params['file_path']);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $start_row = 3;
        $butir = $spreadsheet->getActiveSheet(0)->getCell('A' . $start_row)->getCalculatedValue();
        $butir = str_replace('.', '', $butir);
        $ins_component_id = trim($spreadsheet->getActiveSheet(0)->getCell('I' . strval($start_row))->getCalculatedValue());
        $main_component_id = trim($spreadsheet->getActiveSheet(0)->getCell('M' . strval($start_row))->getCalculatedValue());

        $obj_instrument = new \ArrayObject();
        while (is_numeric($butir)) {
            $butir = $spreadsheet->getActiveSheet(0)->getCell('A' . $start_row)->getCalculatedValue();
            $butir = str_replace('.', '', $butir);
            $value = trim($spreadsheet->getActiveSheet()->getCell('H' . strval($start_row))->getCalculatedValue());
            $ins_component_id = trim($spreadsheet->getActiveSheet(0)->getCell('M' . strval($start_row))->getCalculatedValue());
            if (!empty($ins_component_id)) {
                $aspect_id = trim($spreadsheet->getActiveSheet(0)->getCell('N' . strval($start_row))->getCalculatedValue());
                $instrument_component = InstrumentComponent::find($ins_component_id);
                    //->where('type', '=', 'main')->first();
                if (is_object($instrument_component)) {
                    $main_component_id = $instrument_component->id;
                } 
                $instrument_aspect = InstrumentAspect::find($aspect_id);
                $aspect = '-';
                if (is_object($instrument_aspect)) {
                    $aspect = $instrument_aspect->aspect;
                }
                $instrument_aspect_point = InstrumentAspectPoint::query()
                    ->where('instrument_aspect_id', '=', $aspect_id)
                    ->where('value', '=', $value)->first();
                $statement = '-'; $instrument_aspect_point_id = '';
                if (is_object($instrument_aspect_point)) {
                    $statement = $instrument_aspect_point->statement;
                    $value = $instrument_aspect_point->value;
                    $instrument_aspect_point_id = $instrument_aspect_point->id;
                } else {
                    $value = 0;
                }
                $accre_content = new AccreditationContent();
                $accre_content->aspectable_id = $aspect_id;
                $accre_content->main_component_id = $main_component_id;
                $accre_content->instrument_aspect_point_id = $instrument_aspect_point_id;
                if($instrument_aspect_point_id==''){
                    $accre_content->aspectable_type = 'App\Models\InstrumentComponent';
                }else{
                    $accre_content->aspectable_type = 'App\Models\InstrumentAspect';
                }
                
                $accre_content->aspect = $aspect;
                $accre_content->statement = $statement;
                $accre_content->value = $value;
                $accre_content->accreditation_proposal_id = $params['accreditation_proposal_id'];
                $accre_content->butir = $butir;
                if ($aspect_id != '') {
                    $accre_content->save();
                }
                $obj_instrument->append($accre_content);
            }
            $start_row++;
        }
        return $obj_instrument->getArrayCopy();
        ;
    }

    public function destroyFile($id)
    {
        $accre_file = AccreditationProposalFiles::where('id', '=', $id)
            ->delete();
        return $this->sendResponse([], 'Delete succesfull', $accre_file);
    }

    public function showFile($id)
    {
        $accre_file = AccreditationProposalFiles::find($id);
        if (is_object($accre_file)) {
            $file_path = $accre_file->file_path;
            $file_name = $accre_file->file_name;
            $file_type = $accre_file->file_type;
            try {
                $file_content = Storage::get($file_path);
                return response($file_content, 200)
                    ->header('Content-Type', $file_type) // Set Content-Type header
                    ->headers('Access-Control-Expose-Headers', 'Content-Disposition, Content-Type')
                    ->header('Content-Disposition', 'attachment; filename="' . $file_name . '"');
                //return Storage::download($file_path, $accre_file->file_name);
            } catch (FileNotFoundException $e) {
                return $this->sendError('File not Found', 'File not available in hard drive!');
            }

        } else {
            return $this->sendError('Record not Found', 'Record not available in database!');
        }

    }
}
