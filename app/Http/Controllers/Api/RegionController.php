<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegionCollection;
use App\Http\Resources\RegionResource;
use App\Models\Region;
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
class RegionController extends BaseController //implements ICrud
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
	public function destroy(Region $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Region Deleted!', $model->count());
	}

	public function index()
	{		
			$Region = Region::query()
			->get();			
		
		return $this->sendResponse(new RegionResource($Region), "Success", $Region->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Region::query();
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
		$Region = Region::find($id);
		if(is_null($Region)){
			return $this->sendError('Region not found!');
		}
		return $this->sendResponse(new RegionResource($Region), 'Region Available', $Region->count());
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
			'type' => 'required',
            'province_id' => 'required'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$institution = Region::create($input);
		return $this->sendResponse(new RegionResource($institution), 'Region Created', $institution->count);
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, Region $model)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'name' => 'required',
            'type' => 'required',
            'province_id' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->name = $input['name'];
        $model->type = $input['type'];
        $model->province_id = $input['province_id'];		
		$model->update();

		return $this->sendResponse(new RegionResource($model), 'Region Updated!', $model->count());
	}

}
?>