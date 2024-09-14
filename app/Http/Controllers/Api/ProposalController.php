<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AccreditationProposalResource;
use Illuminate\Http\Request;
use App\Models\AccreditationProposal;
use App\Models\ProposalState;
use App\Models\AccreditationProposalFiles;
use App\Models\ProposalDocument;
use App\Models\Instrument;
use App\Models\Province;
use App\Models\Region;
use App\Models\ProvinceRegion;
use Illuminate\Support\Facades\Validator;
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
        $proposal_files = AccreditationProposalFiles::find()->where('accreditation_proposal_id','=', $model->id)->get();
        //delete files
        foreach($proposal_files as $file){
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
            ->join('provinces', 'institution_requests.province_id', '=', 'provinces.id')
            ->join('cities', 'institution_requests.city_id', '=', 'cities.id')
            ->join('subdistricts', 'institution_requests.subdistrict_id', '=', 'subdistricts.id')
            ->join('villages', 'institution_requests.village_id', '=', 'villages.id')
            ->select(['accreditation_proposals.*',
                'proposal_states.state_name',
                'institution_requests.category',
                'library_name',
                'npp',
                'agency_name',
                'institution_head_name',
                'email',
                'telephone_number',
                'provinces.name as province',
                'cities.name as city',
                'subdistricts.name as subdistrict',
                'villages.name as village',
                'villages.postal_code']);
        if ($s = $request->input(key: 'search')) {//filter berdasarkan name            
            $query->where('institution_requests.library_name', 'like', "%{$s}%");
        }
        if ($s = $request->input(key: 'province')) {//filter berdasarkan name            
            $query->where('provinces.name', '=', "{$s}");
        }
        if ($s = $request->input(key: 'city')) {//filter berdasarkan name            
            $query->where('cities.name', '=', "{$s}");
        }
        if ($s = $request->input(key: 'subdistrict')) {//filter berdasarkan name            
            $query->where('subdistricts.name', '=', "{$s}");
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
            ->with('accreditationProposalFiles')            
            ->get();
        //$accre_files = AccreditationProposalFiles::query();


        if (is_null($accreditation)) {
            return $this->sendError('Proposal not found!');
        }
        return $this->sendResponse(new AccreditationProposalResource($accreditation), 'Proposal Available', $accreditation->count());
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

    public function storeFiles(Request $request)
    {        
        $input = $request->all();
        $validator = Validator::make($input, [
            'file' => 'required|mimes:pdf, xlsx| max:2048',
            'accreditation_proposal_id' => 'required',
            'proposal_document_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        if($request->file()){
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getClientMimeType();
            $file_path = '/storage/' . $request->file('file')->storeAs($request->accreditation_proposal_id, $file_name, 'public');
            $accre_files = AccreditationProposalFiles::query()
                ->where('accreditation_proposal_id', '=', $request->accreditation_proposal_id)
                ->where('proposal_document_id', '=', $request->proposal_document_id)->first();
            if(is_object($accre_files)){
                $accre_files->accreditation_proposal_id = $request->accreditation_proposal_id;
                $accre_files->proposal_document_id = $request->proposal_document_id;
                $accre_files->file_name = $file_name;
                $accre_files->file_type = $file_type;
                $accre_files->file_path = $file_path;
                $accre_files->update();
            }else{
                $data = [
                    'accreditation_proposal_id' => $request->accreditation_proposal_id,
                    'proposal_document_id' => $request->proposal_document_id,
                    'file_name' => $request->proposal_document_id,
                    'file_type' => $file_type,
                    'file_path' => $file_path
                ];
                
                $accre_files = AccreditationProposalFiles::create($data);
            }
        }else{
            return $this->sendError('File Error!', $validator->errors());        
        }
        return $this->sendResponse(new AccreditationProposalResource($accre_files), 'Success', $accre_files->count());
    }

    /**
     * @param $request
     * @param $model
     * 
     * @param request
     * @param model
     */
    public function update(Request $request, AccreditationProposal $model)
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
            'certificate_file' => 'nullable',
            'recommendation_file' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        
        $model->update($input);

        return $this->sendResponse(new AccreditationProposalResource($model), 'Accreditation Updated!', $model->count());
    }
}
