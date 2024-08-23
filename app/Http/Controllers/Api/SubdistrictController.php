<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SubdistrictResource;
use App\Models\Subdistrict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SubdistrictController extends BaseController
{
    /**
	 * @param $model
	 * 
	 * @param model
	 */
	public function destroy(Subdistrict $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Subdistrict Deleted!', $model->count());
	}

	public function index()
	{		
			$Subdistrict = Subdistrict::query()
			->get();			
		
		return $this->sendResponse(new SubdistrictResource($Subdistrict), 'Success', $Subdistrict->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Subdistrict::query();
		if($s = $request->input(key:'s')){//filter berdasarkan name 
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

    public function cityId(Request $request, $city_id)//with filter
	{
		$query = Subdistrict::query();		
        $query->where('city_id', '=', $city_id);			
		
		$response = $query->get();
        $total = $query->count();
		return $this->sendResponse($response, "Success", $total);		
	}

	/**
	 * @param $id
	 * 
	 * @param id
	 */
	public function show($id)
	{
		$Subdistrict = Subdistrict::find($id);
		if(is_null($Subdistrict)){
			return $this->sendError('Subdistrict not found!');
		}
		return $this->sendResponse(new SubdistrictResource($Subdistrict), 'Subdistrict Available', $Subdistrict->count());
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
			
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$institution = Subdistrict::create($input);
		return $this->sendResponse(new SubdistrictResource($institution), 'Subdistrict Created', $institution->count);
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, Subdistrict $model)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'name' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->name = $input['name'];		
		$model->update();

		return $this->sendResponse(new SubdistrictResource($model), 'Subdistrict Updated!', $model->count());
	}
}
