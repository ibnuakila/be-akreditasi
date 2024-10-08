<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalDocumentResource;
use App\Models\ProposalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalDocumentController extends BaseController //implements ICrud
{
    //
    public function store(Request $request){
        $input = $request->all();
		//validating---------------------------
		$validator = Validator::make($input, [			
            'document_name' => 'required',
            'option' => 'nullable',
            'document_format' => 'nullable',
            'instrument_id' => 'required',
            'instrument_component_id' => 'nullable'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$document = ProposalDocument::create($input);
		return $this->sendResponse(new ProposalDocumentResource($document), 'Proposal Document Created', $document->count);
    }

    public function update(Request $request, ProposalDocument $model){
        $input = $request->all();
		//validating---------------------------
		$validator = Validator::make($input, [			
            'document_name' => 'required',
            'option' => 'nullable',
            'document_format' => 'nullable',
            'instrument_id' => 'required',
            'instrument_component_id' => 'nullable'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
        $model->document_name = $input['document_name'];
        $model->instrument_id = $input['instrument_id'];
        $model->instrument_component_id = $input['instrument_component_id'];
        $model->update();
        return $this->sendResponse(new ProposalDocumentResource($model), 'Proposal Document Updated', $model->count);
    }

    public function destroy(ProposalDocument $model){
        $model->delete();
		return $this->sendResponse([], 'Proposal Document Deleted!', $model->count());
    }

    public function index(){
        $document = ProposalDocument::query()
			->get();			
		
		return $this->sendResponse(new ProposalDocumentResource($document), "Success", $document->count());
    }

    public function list(Request $request)//with filter
	{
		$query = ProposalDocument::query();
		if($s = $request->input(key:'s')){//filter berdasarkan name 
			$query->where('document_name', 'like', "%{$s}%");			
		}
        if($s = $request->input(key:'instrument_id')){
            $query->where('instrument_id', '=', $s);
        }
		$perPage = 15;
		$page = $request->input(key:'page', default:1);
		$total = $query->count();
		$response = $query->offset(value:($page - 1) * $perPage)
			->limit($perPage)
				->paginate();
		return $this->sendResponse($response, "Success", $total);		
	}

    public function show($id){
        $document = ProposalDocument::find($id);
		if(is_null($document)){
			return $this->sendError('Proposal Document not found!');
		}
		return $this->sendResponse(new ProposalDocumentResource($document), 'Proposal Document Available', $document->count());
    }
}
