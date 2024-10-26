<?php

namespace App\Http\Controllers\Api;

use App\Models\InstrumentAspect;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentAspectController extends BaseController implements ICrud
{
    //
    public function addNew($instrument_id){
        $insComponent = InstrumentComponent::where('instrument_id', '=', $instrument_id)
            ->where('type', '=', 'main')->get();
        $data['instrument_components'] = $insComponent;
        return $this->sendResponse($data, 'Success', $insComponent->count());
    }
    public function destroy($id) {
        $InsAspect = InstrumentAspect::find($id);
        if(is_object($InsAspect)){
            $ret = $InsAspect->delete();
            return $this->sendResponse([], "Delete successful", $ret);
        }else{
            return $this->sendError('Error', 'Delete fail');
        }
    }

    public function index() {
        $instrumentAspects = InstrumentAspect::query()
            ->paginate();
        return $this->sendResponse($instrumentAspects, "Success", $instrumentAspects->count());
    }

    public function list(Request $request, $instrument_id) {//with filter
        $query = InstrumentAspect::query()
        ->where('instrument_id', '=', $instrument_id);
        if ($s = $request->input(key: 's')) {//filter berdasarkan library_name atau agency_name
            $query->where('aspect', 'like', "%{$s}%");
                    //->orWhere('agency_name', 'like', "%{$s}%");
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
        $InsAspect = InstrumentAspect::find($id);
        if(is_object($InsAspect)){            
            return $this->sendResponse($InsAspect, "Success", $InsAspect->count());
        }else{
            return $this->sendError('Error', 'Object not found!');
        }
    }

    public function store(Request $request) {
        $input = $request->all();
        $valid = Validator::make($input,
        [
            'instrument_id' => 'required',
            'aspect' => 'required',
            'instrument_component_id' => 'required',
            'type' => 'nullable',
            'order' => 'nullable',
            'statement_a' => 'required',
            'statement_b' => 'required',
            'statement_c' => 'required',
            'statement_d' => 'required',            
            'parent_id' => 'nullable'
        ]);
        if($valid->fails()){
            return $this->sendError('Error', $valid->errors());
        }
        $data = [
            'instrument_id' => $input['instrument_id'],
            'aspect' => $input['aspect'],
            'instrument_component_id' => $input['instrument_component_id'],
            'type' => $input['type'],
            'order' => $input['order'],
            //'statement' => $input['statement'],
            //'value' => $input['value'],
            'parent_id' => $input['parent_id']
        ];
        $insAspect = InstrumentAspect::create($data);
        return $this->sendResponse($insAspect, 'Success', $insAspect->count());
    }

    public function update(Request $request, $id) {
        $input = $request->all();
        $valid = Validator::make($input,
        [

        ]);
        if($valid->fails()){
            return $this->sendError('Error', $valid->errors());
        }
        
        $insAspect = InstrumentAspect::find($id);

        $insAspect->save();
        return $this->sendResponse($insAspect, 'Success', $insAspect->count());
    }
}
