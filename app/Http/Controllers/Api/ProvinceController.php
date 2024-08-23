<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\InstitutionResource;
use App\Http\Resources\ProvinceResource;
use App\Models\Province;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


use Controllers\Api;
/**
 * @author ibnua
 * @version 1.0
 * @created 11-Aug-2024 10:48:12 AM
 */
class ProvinceController extends BaseController //implements ICrud
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
	public function destroy(Province $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Province Deleted!', $model->count());
	}

	public function index()
	{		
			$province = Province::query()
			->get();			
		
		return $this->sendResponse(new ProvinceResource($province), 'Success', $province->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Province::query();
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

	/**
	 * @param $id
	 * 
	 * @param id
	 */
	public function show($id)
	{
		$province = Province::find($id);
		if(is_null($province)){
			return $this->sendError('Province not found!');
		}
		return $this->sendResponse(new ProvinceResource($province), 'Province Available', $province->count());
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
		$institution = Province::create($input);
		return $this->sendResponse(new ProvinceResource($institution), 'Province Created', $institution->count);
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

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->name = $input['name'];		
		$model->update();

		return $this->sendResponse(new ProvinceResource($model), 'Province Updated!', $model->count());
	}

}
?>