<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VillageResource;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class VillageController extends BaseController
{
    /**
	 * @param $model
	 * 
	 * @param model
	 */
	public function destroy(Village $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Village Deleted!', $model->count());
	}

	public function index()
	{		
			$Village = Village::query()
			->get();			
		
		return $this->sendResponse(new VillageResource($Village), "Success", $Village->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Village::query();
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

    public function getBySubdistrictId(Request $request, $subdistrict_id)//with filter
	{
		$query = Village::query();		
        $query->where('subdistrict_id', '=', $subdistrict_id);			
		
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
		$Village = Village::find($id);
		if(is_null($Village)){
			return $this->sendError('Village not found!');
		}
		return $this->sendResponse(new VillageResource($Village), 'Village Available', $Village->count());
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
		$institution = Village::create($input);
		return $this->sendResponse(new VillageResource($institution), 'Village Created', $institution->count);
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, Village $model)
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

		return $this->sendResponse(new VillageResource($model), 'Village Updated!', $model->count());
	}
}
