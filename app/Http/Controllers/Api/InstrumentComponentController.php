<?php

namespace App\Http\Controllers\Api;

use App\Models\Instrument;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstrumentComponentController extends BaseController implements ICrud
{
    //
    public function addNew(Request $request, $instrument_id){
        
        $instrument = Instrument::find($instrument_id);
        if(is_object($instrument)){
            $data['instrument'] = $instrument;
            $data['type'] = [
                ['main' => 'Main'], 
                ['sub_1' => 'Sub 1'], 
                ['sub_2' => 'Sub 2']];
        $query = InstrumentComponent::query()
            ->where('instrument_id', '=', $instrument_id)
            ->where('type', '=', 'main');
        if ($s = $request->input(key: 'search')) {//filter berdasarkan library_name atau agency_name
            $query->where('name', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%");
        }
        //$perPage = $request->input(key: 'pageSize', default: 10);
        //$page = $request->input(key: 'page', default: 1);
        //$total = $query->count();
        $response = $query->get();
            // $query->offset(value: ($page - 1) * $perPage)
            //     ->limit($perPage)
            //     ->paginate();
            $data['components'] = $response;
            return $this->sendResponse($data, "Success", $total);
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
            'category' => 'required',
            'name' => 'required',
            'weight' => 'nullable',
            'type' => 'required',
            'instrument_id' => 'required',
            'parent_id' => 'nullable'
        ]);
        if($valid->fails()){
            $this->sendError('Error', $valid->errors());
        }
        $data = [
            'category' => $input['category'],
            'name' => $input['name'],
            'weight' => $input['weight'],
            'type' => $input['type'],
            'instrument_id' => $input['instrument_id'],
            'parent_id' => $input['parent_id'],
            'order' => 1
        ];
        $findObj = InstrumentComponent::where('instrument_id', '=', $input['instrument_id'])
            ->where('category', '=', $input['category'])
            ->where('name', '=', $input['name'])
            ->where('type', '=', $input['type'])->first();
        if(is_object($findObj)){
            $findObj->category = $input['category'];
            $findObj->name = $input['name'];
            $findObj->weight = $input['weight'];
            $findObj->type = $input['type'];
            $findObj->instrument_id = $input['instrument_id'];
            $findObj->update();
            $insAspect = $findObj;
        }else{
            $insAspect = InstrumentComponent::create($data);
        }
        
        return $this->sendResponse($insAspect, 'Success', 1);
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

    public function addSubComponent(){

    }

    public function getComponent($parent_id, $type){
        $InsComponent = InstrumentComponent::where('parent_id', '=', $parent_id)
            ->where('type', '=', $type)->get();
        if($InsComponent->count()>0){
            $data[$type.'_components'] = $InsComponent;
            return $this->sendResponse($data, "Success", $InsComponent->count());
        }else{
            return $this->sendResponse([], 'Record not found', 0);
        }
    }

    public function getMainComponent($instrument_id){
        $InsComponent = InstrumentComponent::where('instrument_id', '=', $instrument_id)
            ->where('type', '=', 'main')->get();
        if($InsComponent->count()>0){
            $data['main_components'] = $InsComponent;
            return $this->sendResponse($data, "Success", $InsComponent->count());
        }else{
            return $this->sendError('Failed', 'Record not found');
        }
    }
}
