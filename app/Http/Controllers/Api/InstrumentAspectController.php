<?php

namespace App\Http\Controllers\Api;

use App\Models\InstrumentAspect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentAspectController extends BaseController implements ICrud
{
    //
    public function destroy($id) {
        $InsAspect = InstrumentAspect::find($id);
        if(is_object($InsAspect)){
            $ret = $InsAspect->delete();
            $this->sendResponse([], "Delete successful", $ret);
        }else{
            $this->sendError('Error', 'Delete fail');
        }
    }

    public function index() {
        $instrumentAspects = InstrumentAspect::query()
            ->paginate();
        $this->sendResponse($instrumentAspects, "Success", $instrumentAspects->count());
    }

    public function list(Request $request) {//with filter
        $query = InstrumentAspect::query();
        if ($s = $request->input(key: 's')) {//filter berdasarkan library_name atau agency_name
            $query->where('library_name', 'like', "%{$s}%")
                    ->orWhere('agency_name', 'like', "%{$s}%");
        }
        $perPage = 15;
        $page = $request->input(key: 'page', default: 1);
        $total = $query->count();
        $response = $query->offset(value: ($page - 1) * $perPage)
                ->limit($perPage)
                ->paginate();
        return $this->sendResponse($response, $total);
    }

    public function show($id) {
        $InsAspect = InstrumentAspect::find($id);
        if(is_object($InsAspect)){            
            $this->sendResponse($InsAspect, "Success", $InsAspect->count());
        }else{
            $this->sendError('Error', 'Object not found!');
        }
    }

    public function store(Request $request) {
        $input = $request->all();
        $valid = Validator::make($input,
        [

        ]);
        if($valid->fails()){
            $this->sendError('Error', $valid->errors());
        }
        $data = [];
        $insAspect = InstrumentAspect::create($data);
        $this->sendResponse($insAspect, 'Success', $insAspect->count());
    }

    public function update(Request $request, $id) {
        $input = $request->all();
        $valid = Validator::make($input,
        [

        ]);
        if($valid->fails()){
            $this->sendError('Error', $valid->errors());
        }
        
        $insAspect = InstrumentAspect::find($id);

        $insAspect->save();
        $this->sendResponse($insAspect, 'Success', $insAspect->count());
    }
}
