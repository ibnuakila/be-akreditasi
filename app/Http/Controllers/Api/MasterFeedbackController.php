<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\models\MasterFeedback;

class MasterFeedbackController extends Controller
{
    public function destroy(MasterFeedback $model)
    {
        $model->delete();
		return $this->sendResponse([], 'Assessor Deleted!', $model->count());
    }

	public function index()
    {
        $query = MasterFeedback::query();
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
        $feedback = MasterFeedback::find($id);
		if(is_null($feedback)){
			return $this->sendError('Feedback not found!');
		}
		return $this->sendResponse(($feedback), 'Feedback Available', $feedback->count());
    }

	
	public function store(Request $request){
        $input = $request->all();
		//validating---------------------------
		$validator = Validator::make($input, [
			'feedback' => 'required'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$feedback = MasterFeedback::create($input);
		return $this->sendResponse(($feedback), 'Feedback Created', $feedback->count);
    }

	
	public function update(Request $request, MasterFeedback $model)
    {
        $input = $request->all();

		$validator = Validator::make($input, [
			'feedback' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->feedback = $input['feedback'];	        
		$model->update();

		return $this->sendResponse(($model), 'Feedback Updated!', $model->count());
    }
}
