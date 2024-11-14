<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AccreditationProposalResource;
use App\Models\AccreditationContent;
use App\Models\InstitutionRequest;
use ArrayObject;
use File;
use Illuminate\Http\Request;
use App\Models\AccreditationProposal;
use App\Models\ProposalState;
use App\Models\AccreditationProposalFiles;
use App\Models\ProposalDocument;
use App\Models\Instrument;
use App\Models\Province;
use App\Models\Region;
use App\Models\ProvinceRegion;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
class ProposalController extends BaseController
{

    function __construct()
    {
    }

    function __destruct()
    {
    }



    /**
     * @param $model
     * 
     * @param model
     */
    public function destroy(AccreditationProposal $model)
    {
        $proposal_files = AccreditationProposalFiles::find()->where('accreditation_proposal_id', '=', $model->id)->get();
        //delete files
        foreach ($proposal_files as $file) {
            $file->delete();
        }
        $model->delete();
        return $this->sendResponse([], 'Proposal Deleted!', $model->count());
    }

    public function index()
    {
        $accreditation = AccreditationProposal::query()
            ->get();

        return $this->sendResponse(new AccreditationProposalResource($accreditation), 'Success', $accreditation->count());
    }

    public function list(Request $request)//with filter
    {
        $query = AccreditationProposal::query()
            ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
            ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
            //->join('provinces', 'institution_requests.province_id', '=', 'provinces.id')
            //->join('cities', 'institution_requests.city_id', '=', 'cities.id')
            //->join('subdistricts', 'institution_requests.subdistrict_id', '=', 'subdistricts.id')
            //->join('villages', 'institution_requests.village_id', '=', 'villages.id')
            ->select([
                'accreditation_proposals.*',
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
                'village_name as village'
            ]);
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
        $accreditation = AccreditationProposal::query()
            ->where(['id' => $id])
            ->with('proposalState')
            //->with('institutionRequest')            
            ->get();
        $institution_request = InstitutionRequest::query()
            ->where(['accreditation_proposal_id' => $id])
            ->with('province')
            ->with('city')
            ->with('subDistrict')
            ->with('village')
            ->get();
        $accre_files = AccreditationProposalFiles::query()
            ->where(['accreditation_proposal_id' => $id])
            ->with('proposalDocument')
            ->get();
        //$accre_contents = AccreditationContent::query()
        /*$accre_contents = DB::table('accreditation_contents')
            ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
            ->select(
                'instrument_components.name',
                'instrument_components.weight',
                DB::raw('SUM(value) as nilai_sa'),
                DB::raw('(SUM(value) * instrument_components.weight) / 100 as total_nilai_sa'),
                DB::raw('SUM(0) as nilai_evaluasi'),
                DB::raw('SUM(0) as total_nilai_evaluasi'),
                'main_component_id'
            )
            ->where('accreditation_proposal_id', $id)
            ->groupBy('main_component_id', 'instrument_components.name', 'instrument_components.weight')
            ->get();*/
        /*$accre_contents = DB::table('evaluation_contents')
        ->join('accreditation_contents', 'evaluation_contents.accreditation_content_id', '=', 'accreditation_contents.id')
        ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
        ->select(
            'instrument_components.name',
            'instrument_components.weight',
            DB::raw('SUM(accreditation_contents.value) as nilai_sa'),
            DB::raw('(SUM(accreditation_contents.value) * instrument_components.weight) / 100 as total_nilai_sa'),
            DB::raw('SUM(evaluation_contents.value) as nilai_evaluasi'),
            DB::raw('(SUM(evaluation_contents.value) * instrument_components.weight) / 100 as total_nilai_evaluasi'),
            'accreditation_contents.main_component_id'
        )
        ->where('accreditation_proposal_id', $id)
        ->groupBy('accreditation_contents.main_component_id', 'instrument_components.name', 'instrument_components.weight')
        ->get();*/

        $accre_contents = DB::table('accreditation_contents')
            ->select(
                'instrument_components.name',
                'instrument_components.weight',
                DB::raw('SUM(accreditation_contents.value) as nilai_sa'),
                DB::raw('(SUM(accreditation_contents.value) * instrument_components.weight) / 100 as total_nilai_sa'),
                DB::raw('SUM(evaluation_contents.value) as nilai_evaluasi'),
                DB::raw('(SUM(evaluation_contents.value) * instrument_components.weight) / 100 as total_nilai_evaluasi'),
                'accreditation_contents.main_component_id'
            )
            ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
            ->leftJoin('evaluation_contents', 'accreditation_contents.id', '=', 'evaluation_contents.accreditation_content_id')
            ->where('accreditation_contents.accreditation_proposal_id', $id)
            ->groupBy('accreditation_contents.main_component_id','instrument_components.name','instrument_components.weight')
            ->get();

        $proposal_states = ProposalState::all();
        $is_valid = [
            ['label' => 'Valid', 'value' => 'valid'],
            ['label' => 'Tidak Valid', 'value' => 'tidak_valid']
        ];
        $certicate_status = [
            ['label' => 'Cetak Sertifikat', 'value' => 'cetak_sertifikat'],
            ['label' => 'Dikirim', 'value' => 'dikirim'],
            ['label' => 'Ditandatangani', 'value' => 'ditandatangani'],
            ['label' => 'Terakreditasi', 'value' => 'terakreditasi'],
        ];

        $data['accreditation_proposal'] = $accreditation;
        $data['institution_request'] = $institution_request;
        $data['accreditation_proposal_files'] = $accre_files;
        $data['proposal_states'] = $proposal_states;
        $data['certificate_status'] = $certicate_status;
        $data['is_valid'] = $is_valid;
        $data['accreditation_evaluation'] = $accre_contents;


        if (is_null($accreditation)) {
            return $this->sendError('Proposal not found!');
        }
        return $this->sendResponse($data, 'Proposal Available', $accreditation->count());
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
            'recommendation_file' => 'nullable',
            'is_valid' => 'required',
            'instrument_id' => 'required',
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $proposal = AccreditationProposal::create($input);
        return $this->sendResponse(new AccreditationProposalResource($proposal), 'Proposal Created', $proposal->count);
    }



    /**
     * @param $request
     * @param $model
     * 
     * @param request
     * @param model
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();

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
            'certificate_file' => ['required', 'extensions:pdf', 'max:2048'],
            'recommendation_file' => ['required', 'extensions:pdf', 'max:2048'],
            'is_valid' => 'required',
            'instrument_id' => 'required',
            'category' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $model = AccreditationProposal::find($id);
        if (is_object($model)) {
            $model->institution_id = $input['institution_id'];
            $model->proposal_date = $input['proposal_date'];
            $model->proposal_state_id = $input['proposal_state_id'];
            $model->finish_date = $input['finish_date'];
            $model->type = $input['type'];
            $model->notes = $input['notes'];
            $model->accredited_at = $input['accredited_at'];
            $model->predicate = $input['predicate'];
            $model->certificate_status = $input['certificate_status'];
            $model->certificate_expires_at = $input['certificate_expires_at'];
            $model->pleno_date = $input['pleno_date'];
            $model->is_valid = $input['is_valid'];
            $model->instrument_id = $input['instrument_id'];
            $model->category = $input['category'];
            //certificate-file
            if ($request->file()) {
                $directory = 'certifications/' . $model->id;
                $file_certificate = $request->file('certificate_file')->store($directory);
                $model->certificate_file = $file_certificate;//Storage::url($file_certificate);
                $directory = 'recommendations/' . $model->id;
                $file_recommendation = $request->file('recommendation_file')->store($directory);
                $model->recommendation_file = $file_recommendation;//Storage::url($file_recommendation);
            }

            $model->save();
        } else {
            $this->sendError('Error', 'Object not found!');
        }
        return $this->sendResponse(new AccreditationProposalResource($model), 'Accreditation Updated!', $model->count());
    }

    public function showFile($id, $file)
    {
        $accre_file = AccreditationProposal::find($id);

        /*if($file == 'certificate_file'){
            $accre_file->where('certificate_file','=', $file)->first();
        }elseif($file == 'recommendation_file'){
            $accre_file->where('recommendation_file', '=', $file)->first();
        }*/
        //return json_encode($accre_file);
        if (is_object($accre_file)) {
            if ($file == 'certificate_file') {
                $file_path = $accre_file->certificate_file;
                $file_name = 'certificate_file.pdf';
            } elseif ($file == 'recommendation_file') {
                $file_path = $accre_file->recommendation_file;
                $file_name = 'recommendation_file.pdf';
            }
            try {
                $file_content = Storage::get($file_path);
                return response($file_content, 200)
                    ->header('Content-Type', 'application/pdf') // Set Content-Type header
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
