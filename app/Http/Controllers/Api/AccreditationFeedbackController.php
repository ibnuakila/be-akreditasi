<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AccreditationFeedback;
use App\Models\MasterFeedback;

class AccreditationFeedbackController extends BaseController 
{
    public function addNew()
    {
        $response = MasterFeedback::query()->select('*')->get();
        return $this->sendResponse($response, "Success", count($response));	
    }
    
	public function destroy(AccreditationFeedback $model)
    {
        $model->delete();
		return $this->sendResponse([], 'Feedback Deleted!', $model->count());
    }

	public function index(Request $request)
    {
        $query = AccreditationFeedback::query();
            if($s = $request->input(key:'search')){//filter berdasarkan name 
                $query->where('feedback', 'like', "%{$s}%");			
            }
        $perPage = 15;
		$page = $request->input(key:'page', default:1);
		$total = $query->count();
		$response = $query->offset(value:($page - 1) * $perPage)
			->limit($perPage)
				->paginate();
		return $this->sendResponse($response, "Success", $total);	
    }

	
	public function show($id)
    {
        $feedback = AccreditationFeedback::find($id);
		if(!is_object($feedback)){
			return $this->sendError('Feedback not found!');
		}
		return $this->sendResponse(($feedback), 'Feedback Available', $feedback->count());
    }

	
	public function store(Request $request){
        $input = $request->all();
		//validating---------------------------
		$validator = Validator::make($input, [
			'accreditation_proposal_id' => 'required',
            'accreditation_date' => 'nullable',
            'feedback' => 'required'            
		]);
        $feedback = json_decode($request['feedback']);
        foreach($feedback->data as $row){
            $data = [
                'accreditation_proposal_id' => $input['accreditation_proposal_id'],
                'accreditation_date' => $input['accreditation_date'],
                'answer' => $row->answer,
                'note' => $row->note,
                'master_feedback_id' => $row->master_feedback_id,
            ];
            $accre_feedback = AccreditationFeedback::create($data);
        }
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		//
		return $this->sendResponse(($accre_feedback), 'Feedback Created', $accre_feedback->count());
    }

	
	public function update(Request $request, AccreditationFeedback $model)
    {
        $input = $request->all();

		$validator = Validator::make($input, [
			'answer' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->answer = $input['answer'];	        
		$model->update();

		return $this->sendResponse(($model), 'Feedback Updated!', $model->count());
    }
}
