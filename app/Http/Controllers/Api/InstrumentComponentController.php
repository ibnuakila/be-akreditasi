<?php

namespace App\Http\Controllers\Api;

use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentComponentController extends BaseController implements ICrud
{
    //
    public function addNew($instrument_id){
        $InsComponent = InstrumentComponent::where('instrument_id', '=', $instrument_id)
            ->where('type', '=', 'main')->get();
        if($InsComponent->count()>0){
            $data['components'] = $InsComponent;
            return $this->sendResponse($data, "Success", $InsComponent->count());
        }else{
            return $this->sendError('Error', 'Failed');
        }
    }
    public function destroy($id) {
        $InsComponent = InstrumentComponent::find($id);
        if(is_object($InsComponent)){
            $ret = $InsComponent->delete();
            $this->sendResponse([], "Delete successful", $ret);
        }else{
            $this->sendError('Error', 'Delete fail');
        }
    }

    public function index() {
        $insComponent = InstrumentComponent::query()
            ->paginate();
        $this->sendResponse($insComponent, "Success", $insComponent->count());
    }

    public function list(Request $request) {//with filter
        $query = InstrumentComponent::query();       
        if ($s = $request->input(key: 'search')) {//filter berdasarkan library_name atau agency_name
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
        $insComponent = InstrumentComponent::find($id);
        if(is_object($insComponent)){            
            $this->sendResponse($insComponent, "Success", $insComponent->count());
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
        $insAspect = InstrumentComponent::create($data);
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
        
        $insAspect = InstrumentComponent::find($id);

        $insAspect->save();
        $this->sendResponse($insAspect, 'Success', $insAspect->count());
    }

    public function getSubComponent($parent_id, $type){
        $InsComponent = InstrumentComponent::where('parent_id', '=', $parent_id)
            ->where('type', '=', $type)->get();
        if($InsComponent->count()>0){
            $data['components_'.$type] = $InsComponent;
            return $this->sendResponse($data, "Success", $InsComponent->count());
        }else{
            return $this->sendError('Error', 'Failed');
        }
    }
}
