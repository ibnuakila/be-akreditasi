<?php

namespace App\Http\Controllers\Api;

use App\Models\InstrumentAspectPoint;
use Illuminate\Http\Request;

class InstrumentAspectPointController extends BaseController implements ICrud
{
    //
    public function destroy($id) {
        $InsAspect = InstrumentAspectPoint::find($id);
        if(is_object($InsAspect)){
            $ret = $InsAspect->delete();
            $this->sendResponse([], "Delete successful", $ret);
        }else{
            $this->sendError('Error', 'Delete fail');
        }        
    }

    public function index() {
        $instrumentAspects = InstrumentAspectPoint::query()
            ->paginate();
        $this->sendResponse($instrumentAspects, "Success", $instrumentAspects->count());
    }

    public function list(Request $request) {//with filter
        $query = InstrumentAspectPoint::query();
        if ($s = $request->input(key: 's')) {//filter berdasarkan library_name atau agency_name
            $query->where('library_name', 'like', "%{$s}%")
                    ->orWhere('agency_name', 'like', "%{$s}%");
        }
        $perPage = $request->input(key: 'pageSize', default: 10);
        $page = $request->input(key: 'page', default: 1);
        $total = $query->count();
        $response = $query->offset(value: ($page - 1) * $perPage)
                ->limit($perPage)
                ->paginate();
        return $this->sendResponse($response, $total);
    }

    public function show($id) {
        $InsAspect = InstrumentAspectPoint::find($id);
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
