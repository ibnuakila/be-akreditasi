<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\InstitutionResource;
use App\Models\Institution;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

//require_once ('ICrud.php');



use Controllers\Api;
/**
 * @author ibnua
 * @version 1.0
 * @created 11-Aug-2024 10:48:12 AM
 */
class InstitutionController extends BaseController //implements ICrud
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
	public function destroy(Institution $model)
	{
		$model->delete();
		return $this->sendResponse([], 'Institution Deleted!');
	}

	public function index()
	{		
			$institutions = Institution::query()
			->get();			
		
		return $this->sendResponse(new InstitutionResource($institutions), 'Count: '.$institutions->count());
	}

	public function list(Request $request)//with filter
	{
		$query = Institution::query();
		if($s = $request->input(key:'s')){//filter berdasarkan library_name atau agency_name
			$query->where('library_name', 'like', "%{$s}%")
			->orWhere('agency_name', 'like', "%{$s}%");
		}
		$perPage = 10;
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
		$institution = Institution::find($id);
		if(is_null($institution)){
			return $this->sendError('Institution not found!');
		}
		return $this->sendResponse(new InstitutionResource($institution), 'Institution Available');
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
			'region_id' => 'required',
			'province_id' => 'required',
			'city_id' => 'required',
			'subdistrict_id' => 'required',
			'village_id' => 'required',
			'library_name' => 'required',
			'agency_name' => 'required',
			'category' => 'nullable',
			'npp' => 'nullable',
			'typologi' => 'nullable',
			'address' => 'nullable',
			'institution_head_name' => 'required',
			'email' => 'required',
			'telephone_number' => 'nullable',
			'mobile_number' => 'required',
			'library_head_name' => 'required',
			'library_worker_name' => 'nullable',
			'registration_form_file' => 'nullable',
			'title_count' => 'nullable',
			'status' => 'required',
			'last_predicate' => 'nullable',
			'last_certification_date' => 'nullable',
			'predicate' => 'nullable',
			'accredited_at' => 'nullable'
		]);
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		$institution = Institution::create($input);
		return $this->sendResponse(new InstitutionResource($institution), 'Institution Created');
	}

	/**
	 * @param $request
	 * @param $model
	 * 
	 * @param request
	 * @param model
	 */
	public function update(Request $request, Institution $model)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'region_id' => 'required',
			'province_id' => 'required',
			'city_id' => 'required',
			'subdistrict_id' => 'required',
			'village_id' => 'required',
			'library_name' => 'required',
			'agency_name' => 'required',
			'category' => 'nullable',
			'npp' => 'nullable',
			'typology' => 'nullable',
			'address' => 'nullable',
			'institution_head_name' => 'required',
			'email' => 'required',
			'telephone_number' => 'nullable',
			'mobile_number' => 'required',
			'library_head_name' => 'required',
			'library_worker_name' => 'nullable',
			'registration_form_file' => 'nullable',
			'title_count' => 'nullable',
			'status' => 'required',
			'last_predicate' => 'nullable',
			'last_certification_date' => 'nullable',
			'predicate' => 'nullable',
			'accredited_at' => 'nullable'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->region_id = $input['region_id'];
		$model->province_id = $input['province_id'];
		$model->city_id = $input['city_id'];
		$model->subdistrict_id = $input['subdistrict_id'];
		$model->village_id = $input['village_id'];
		$model->library_name = $input['library_name'];
		$model->agency_name = $input['agency_name'];
		$model->category = $input['category'];
		$model->npp = $input['npp'];
		$model->typology = $input['typology'];
		$model->address = $input['address'];
		$model->institution_head_name = $input['institution_head_name'];
		$model->email = $input['email'];
		$model->telephone_number = $input['telephone_number'];
		$model->mobile_number = $input['mobile_number'];
		$model->library_head_name = $input['library_head_name'];
		$model->library_worker_name = $input['library_worker_name'];
		$model->registration_form_file = $input['registration_form_file'];
		$model->title_count = $input['title_count'];
		$model->status = $input['status'];
		$model->last_predicate = $input['last_predicate'];
		$model->last_certification_date = $input['last_certification_date'];
		$model->predicate = $input['predicate'];
		$model->accredited_at = $input['accredited_at'];
		$model->update();

		return $this->sendResponse(new InstitutionResource($model), 'Institution Updated!');
	}

}
?>