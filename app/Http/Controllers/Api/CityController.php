<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityCollection;
use App\Http\Resources\CityResource;
use App\Models\City;
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
class CityController extends BaseController //implements ICrud
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
	public function destroy(City $model)
	{
		$model->delete();
		return $this->sendResponse([], 'City Deleted!');
	}

	public function index()
	{		
			$city = City::query()
			->get();			
		
		return $this->sendResponse(new CityResource($city), $city->count());
	}

	public function list(Request $request)//with filter
	{
		$query = City::query();
		if($s = $request->input(key:'s')){//filter berdasarkan name 
			$query->where('name', 'like', "%{$s}%");
			
		}
		$perPage = 15;
		$page = $request->input(key:'page', default:1);
		$total = $query->count();
		$response = $query->offset(value:($page - 1) * $perPage)
			->limit($perPage)
				->paginate();
		return $this->sendResponse($response, $total);		
	}

	/**
	 * @param $id
	 * 
	 * @param id
	 */
	public function show($id)
	{
		$City = City::find($id);
		if(is_null($City)){
			return $this->sendError('City not found!');
		}
		return $this->sendResponse(new CityResource($City), 'City Available');
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
		$institution = City::create($input);
		return $this->sendResponse(new CityResource($institution), 'City Created');
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, City $model)
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

		return $this->sendResponse(new CityResource($model), 'City Updated!');
	}

	public function getByProvince($province_id)
	{
		$city = City::query()
			->where('province_id', '=' ,$province_id)->get();
		return $this->sendResponse(new CityResource($city), 'Available', $city->count() );
	}

}
?>