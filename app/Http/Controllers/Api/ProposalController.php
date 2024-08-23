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
        $query = AccreditationProposal::query();
        if ($s = $request->input(key: 's')) {//filter berdasarkan name 
            //$query->join('institution_requests', 'accreditation_proposal.id')
            $query->where('name', 'like', "%{$s}%");

        }
        $perPage = 15;
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
        $accreditation = AccreditationProposal::find($id);
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
        $request->validate([
            'file' => 'required|mimes:pdf, xlsx| max:2048',
            'accreditation_proposal_id' => 'required',
            'proposal_document_id' => 'required'
        ]);
        if($request->file()){
            $file_name = $request->file()->hashName();
            $file_type = $request->file()->getClientMimeType();
            $file_path = '/storage/' . $request->file('file')->storeAs($request->accreditation_proposal_id, $file_name, 'public');
            $accre_files = AccreditationProposalFiles::find()
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
                $accre_files = new AccreditationProposalFiles();
                $accre_files->accreditation_proposal_id = $request->accreditation_proposal_id;
                $accre_files->proposal_document_id = $request->proposal_document_id;
                $accre_files->file_name = $file_name;
                $accre_files->file_type = $file_type;
                $accre_files->file_path = $file_path;
                $accre_files->insert();
            }
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
    public function update(Request $request, Province $model)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $model->name = $input['name'];
        $model->update();

        return $this->sendResponse(new ProvinceResource($model), 'Province Updated!', $model->count());
    }
}
