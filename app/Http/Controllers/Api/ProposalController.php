<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AccreditationProposalResource;
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
            //->join('provinces', 'institution_requests.province_id', '=', 'provinces.id')
            //->join('cities', 'institution_requests.city_id', '=', 'cities.id')
            //->join('subdistricts', 'institution_requests.subdistrict_id', '=', 'subdistricts.id')
            //->join('villages', 'institution_requests.village_id', '=', 'villages.id')
            ->select(['accreditation_proposals.*',
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
                'village_name as village']);
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
        $proposal_states = ProposalState::all();
        $is_valid = [['label' => 'Valid', 'value' => 'valid'], 
            ['label' => 'Tidak Valid', 'value' => 'tidak_valid']];

        $data['accreditation_proposal'] = $accreditation;
        $data['institution_request'] = $institution_request;
        $data['accreditation_proposal_files'] = $accre_files;
        $data['proposal_states'] = $proposal_states;
        $data['is_valid'] = $is_valid;

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
            'recommendation_file' => 'nullable',
            'is_valid' => 'required',
            'instrument_id' => 'nullable',
            'category' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        
        $model->update($input);

        return $this->sendResponse(new AccreditationProposalResource($model), 'Accreditation Updated!', $model->count());
    }
    
    
}
