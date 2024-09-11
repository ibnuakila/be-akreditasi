<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalDocumentResource;
use App\Models\Instrument;
use App\Models\InstrumentComponent;
use App\Models\ProposalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Validator;
class InstrumentController extends BaseController
{
    public function getInstrument(Request $request, $params){
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'Instrument '.$params);
        $activeWorksheet->setCellValue('A2', 'No');
        $activeWorksheet->setCellValue('B2', 'Komponen');

        $ins_com = DB::table('instrument_components')
            ->select('*')
            ->where('type', '=', 'main')
            ->where('category', '=', $params)
            ->get();

        $component_row = 3; $butir = 1; $sub_butir = 1; $sub_sub_butir = 1;
        foreach($ins_com as $component){
            $activeWorksheet->setCellValue('A'. strval($component_row), $butir);
            $activeWorksheet->setCellValue('B'. strval($component_row), $component->name);
            $ins_sub_com = DB::table('instrument_components')
                ->select('*')
                ->where('type', '=', 'sub_1')
                ->where('parent_id', '=', $component->id)
                ->where('category', '=', $params)
                ->get();
            
            $sub_component_row = $component_row;
            foreach($ins_sub_com as $sub_com){
                $activeWorksheet->setCellValue('A'. strval($sub_component_row + 1), $butir.'.'.$sub_butir);
                $activeWorksheet->setCellValue('B'. strval($sub_component_row + 1), ' '.$sub_com->name);
                $ins_sub_sub_com = DB::table('instrument_components')
                    ->select('*')
                    ->where('type', '=', 'sub_2')
                    ->where('parent_id', '=', $sub_com->id)
                    ->where('category', '=', $params)
                    ->get();
                $sub_component_row++; 
                $sub_sub_component_row = $sub_component_row;
                foreach($ins_sub_sub_com as $sub_sub_com){
                    $activeWorksheet->setCellValue('A'. strval($sub_sub_component_row + 1), $butir.'.'.$sub_butir.'.'.$sub_sub_butir);
                    $activeWorksheet->setCellValue('B'. strval($sub_sub_component_row + 1), '  '.$sub_sub_com->name);
                    $sub_sub_component_row++; $sub_sub_butir++;
                    $ins_aspect = DB::table('instrument_components')
                        ->select(['instrument_components.*', 'instrument_aspects.instrument_component_id', 'instrument_aspects.aspect'])
                        ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                        ->where('instrument_components.type', '=', 'sub_2')
                        ->where('instrument_component_id', '=', $sub_sub_com->id)
                        ->where('instrument_components.category', '=', $params)
                        ->get();
                    
                    $ins_com_aspect = $sub_sub_component_row; $butir_aspect = 1;
                    foreach($ins_aspect as $aspect){
                        $activeWorksheet->setCellValue('A'. strval($ins_com_aspect + 1), $butir_aspect);
                        $activeWorksheet->setCellValue('B'. strval($ins_com_aspect + 1), $aspect->aspect);
                        $butir_aspect++;
                        $ins_com_aspect++;
                    }
                    $sub_sub_component_row = $ins_com_aspect;
                    $sub_sub_component_row++;
                    $butir_aspect = 1;
                }
                $sub_component_row = $sub_sub_component_row;
                
                $sub_butir++;
                $sub_sub_butir = 1;
            }
            
            $component_row = $sub_component_row;
            $component_row++;
            $butir++;
            $sub_butir=1;
        }
        
        $writer = new Xlsx($spreadsheet);       
        $response = new StreamedResponse(function() use($writer){
            $writer->save('php://output');
        }); 
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$params.'.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
        //return $this->sendResponse($instrument_com, 'Success', $instrument_com->count());
    }

    public function generateProposalDocument(Request $request)
    {
        $post = $request->all();
        $validator = Validator::make($post, [
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $instrument = Instrument::query()->where('category', '=', $post['category'])->first();
        
        if(is_object($instrument)){
            $instrument_component = InstrumentComponent::query()
            ->where('instrument_id', '=', $instrument->id)
            ->where('type', '=', 'main')
            ->get();
            if(count($instrument_component) > 0){
                foreach($instrument_component as $item){
                    $data = [
                        'document_name' => $item->name,
                        'instrument_id' => $instrument->id,
                        'instrument_component_id' => $item->id
                    ];
                    $return = ProposalDocument::create($data);
                }
                $proposal_document = ProposalDocument::query()
                ->where('instrument_id', '=', $instrument->id)
                ->get();
                return $this->sendResponse(($proposal_document), 'Success', $proposal_document->count());
            }else{
                return $this->sendError('Failed', 'Instrument not available');
            }
        }
    }
}
