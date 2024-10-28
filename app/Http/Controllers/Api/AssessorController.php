<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssessorController extends BaseController
{
    //
    public function destroy(Assessor $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Assessor Deleted!', $model->count());
	}

	public function index(Request $request)
	{		
			$query = Assessor::query();
            if($s = $request->input(key:'search')){//filter berdasarkan name 
                $query->where('name', 'like', "%{$s}%");			
            }
			$result = $query->get();			
		
		return $this->sendResponse(($result), "Success", $result->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Assessor::query();
		if($s = $request->input(key:'search')){//filter berdasarkan name 
			$query->where('name', 'like', "%{$s}%");			
		}
		$perPage = 15;
		$page = $request->input(key:'page', default:1);
		$total = $query->count();
		$response = $query->offset(value:($page - 1) * $perPage)
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
		$assessor = Assessor::find($id);
		if(is_null($assessor)){
			return $this->sendError('Assessor not found!');
		}
		return $this->sendResponse(($assessor), 'Assessor Available', $assessor->count());
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
			'name' => 'required',
			'email' => 'required',
            'phone' => 'nullable',
            'user_id' => 'required'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$assessor = Assessor::create($input);
		return $this->sendResponse(($assessor), 'Assessor Created', $assessor->count);
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, Assessor $model)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'name' => 'required',
			'email' => 'required',
            'phone' => 'nullable',
            'user_id' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->name = $input['name'];	
        $model->email = $input['email']	;
        $model->phone = $input['phone'];
        $model->user_id = $input['user_id'];
		$model->update();

		return $this->sendResponse(($model), 'Assessor Updated!', $model->count());
	}

    public function edit($id){
        $assessor = Assessor::find($id);
		if(is_null($assessor)){
			return $this->sendError('Assessor not found!');
		}
		return $this->sendResponse(($assessor), 'Assessor Available', $assessor->count());
    }
}
