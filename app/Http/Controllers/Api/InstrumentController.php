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
    public function getInstrument(Request $request, $params)
    {

        $instrument = Instrument::find($params);
        if (is_object($instrument)) {
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            $activeWorksheet->getColumnDimension('B')->setWidth(60);
            $activeWorksheet->getColumnDimension('C')->setWidth(12);
            $activeWorksheet->getColumnDimension('D')->setWidth(12);
            $activeWorksheet->getColumnDimension('E')->setWidth(12);
            $activeWorksheet->getColumnDimension('F')->setWidth(12);
            $activeWorksheet->getColumnDimension('G')->setWidth(12);
            $activeWorksheet->getColumnDimension('H')->setWidth(12);
            $activeWorksheet->getColumnDimension('I')->setWidth(8);
            $activeWorksheet->getStyle('I')->getAlignment()->setWrapText(true);
            $activeWorksheet->getColumnDimension('J')->setWidth(8);
            $activeWorksheet->getStyle('J')->getAlignment()->setWrapText(true);
            $activeWorksheet->setCellValue('A1', $params);
            $activeWorksheet->setCellValue('B1', 'Instrument-'.$instrument->category);
            $activeWorksheet->getStyle('B1')->getFont()->setSize(14);
            $activeWorksheet->getStyle('B1')->getFont()->setBold(true);
            $activeWorksheet->setCellValue('A2', 'No');
            $activeWorksheet->setCellValue('B2', 'Komponen');
            $activeWorksheet->setCellValue('C2', '(5)');
            $activeWorksheet->setCellValue('D2', '(4)');
            $activeWorksheet->setCellValue('E2', '(3)');
            $activeWorksheet->setCellValue('F2', '(2)');
            $activeWorksheet->setCellValue('G2', '(1)');
            $activeWorksheet->setCellValue('H2', 'Pilihan');

            $styleArray = [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                    //'rotation' => 90,
                    'startColor' => [
                        'argb' => 'FFA0A0A0',
                    ],
                    'endColor' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],
            ];

            $activeWorksheet->getStyle('A2:H2')->applyFromArray($styleArray);

            $ins_com = DB::table('instrument_components')
                ->select('*')
                ->where('type', '=', 'main')
                ->where('instrument_id', '=', $params)
                ->get();

            $component_row = 3;
            $butir = 1;
            $sub_butir = 1;
            $sub_sub_butir = 1;
            foreach ($ins_com as $component) {
                $activeWorksheet->setCellValue('A' . strval($component_row), $butir);
                $activeWorksheet->setCellValue('B' . strval($component_row), $component->name);
                $ins_sub_com = DB::table('instrument_components')
                    ->select('*')
                    ->where('type', '=', 'sub_1')
                    ->where('parent_id', '=', $component->id)
                    ->where('instrument_id', '=', $params)
                    ->get();

                $sub_component_row = $component_row;
                foreach ($ins_sub_com as $sub_com) {
                    $activeWorksheet->setCellValue('A' . strval($sub_component_row + 1), $butir . '.' . $sub_butir);
                    $activeWorksheet->setCellValue('B' . strval($sub_component_row + 1), '-' . $sub_com->name);
                    $ins_sub_sub_com = DB::table('instrument_components')
                        ->select('*')
                        ->where('type', '=', 'sub_2')
                        ->where('parent_id', '=', $sub_com->id)
                        ->where('instrument_id', '=', $params)
                        ->get();
                    $sub_component_row++;
                    $sub_sub_component_row = $sub_component_row;
                    foreach ($ins_sub_sub_com as $sub_sub_com) {
                        $activeWorksheet->setCellValue('A' . strval($sub_sub_component_row + 1), $butir . '.' . $sub_butir . '.' . $sub_sub_butir);
                        $activeWorksheet->setCellValue('B' . strval($sub_sub_component_row + 1), '--' . $sub_sub_com->name);
                        $sub_sub_component_row++;
                        $sub_sub_butir++;
                        $ins_aspect = DB::table('instrument_components')
                            ->select(['instrument_components.*', 'instrument_aspects.id as aspectable_id', 'instrument_aspects.aspect'])
                            ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                            ->where('instrument_components.type', '=', 'sub_2')
                            ->where('instrument_component_id', '=', $sub_sub_com->id)
                            ->where('instrument_components.instrument_id', '=', $params)
                            ->get();

                        $ins_com_aspect = $sub_sub_component_row;
                        $butir_aspect = 1;
                        foreach ($ins_aspect as $aspect) {
                            $activeWorksheet->setCellValue('A' . strval($ins_com_aspect + 1), $butir_aspect);
                            $activeWorksheet->setCellValue('B' . strval($ins_com_aspect + 1), $aspect->aspect);
                            $activeWorksheet->getStyle('B' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            //option 
                            $ins_aspect_point = DB::table('instrument_components')
                                ->select([
                                    'instrument_components.*',
                                    'instrument_aspects.id as instrument_aspect_id',
                                    'instrument_aspects.instrument_component_id',
                                    'instrument_aspects.aspect',
                                    'instrument_aspect_points.id as instrument_aspect_point_id',
                                    'instrument_aspect_points.statement',
                                    'instrument_aspect_points.value'
                                ])
                                ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                                ->join('instrument_aspect_points', 'instrument_aspects.id', '=', 'instrument_aspect_points.instrument_aspect_id')
                                ->where('instrument_components.type', '=', 'sub_2')
                                ->where('instrument_component_id', '=', $sub_sub_com->id)
                                ->where('instrument_components.instrument_id', '=', $params)
                                ->get();
                            $temp_arr_asp_point = [];
                            $idx_asp_point = 0;
                            foreach ($ins_aspect_point as $asp_point_row) {
                                $temp_arr_asp_point[$idx_asp_point] = $asp_point_row;
                                $idx_asp_point++;
                            }
                            //for($col=0; $col < count($temp_arr_asp_point); $col++){
                            $obj_0 = $temp_arr_asp_point[0];
                            $obj_1 = $temp_arr_asp_point[1];
                            $obj_2 = $temp_arr_asp_point[2];
                            $obj_3 = $temp_arr_asp_point[3];
                            $obj_4 = $temp_arr_asp_point[4];
                            $activeWorksheet->setCellValue('C' . strval($ins_com_aspect + 1), $obj_0->statement);
                            $activeWorksheet->getStyle('C' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('D' . strval($ins_com_aspect + 1), $obj_1->statement);
                            $activeWorksheet->getStyle('D' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('E' . strval($ins_com_aspect + 1), $obj_2->statement);
                            $activeWorksheet->getStyle('E' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('F' . strval($ins_com_aspect + 1), $obj_3->statement);
                            $activeWorksheet->getStyle('F' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('G' . strval($ins_com_aspect + 1), $obj_4->statement);
                            $activeWorksheet->getStyle('G' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->getStyle('H' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('f8fc03');
                            $activeWorksheet->getStyle('H' . strval($ins_com_aspect + 1))
                                ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $activeWorksheet->setCellValue('I' . strval($ins_com_aspect + 1), $aspect->id);
                            $activeWorksheet->setCellValue('J' . strval($ins_com_aspect + 1), $aspect->aspectable_id);
                            //$activeWorksheet->setCellValue('K' . strval($ins_com_aspect + 1), $aspect->instrument_aspect_point_id);
                            //}
                            $butir_aspect++;
                            $ins_com_aspect++;
                        }
                        $sub_sub_component_row = $ins_com_aspect - 1;
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
                $sub_butir = 1;
            }

            $styleBorder = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];
            $activeWorksheet->getStyle('A2:H' . strval($component_row))->applyFromArray($styleBorder);
            $activeWorksheet->getStyle('A3:A' . strval($component_row))->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);


            $writer = new Xlsx($spreadsheet);
            $response = new StreamedResponse(function () use ($writer) {
                $writer->save('php://output');
            });
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $params . '.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        } else {
            return $this->sendError('Failed', 'Instrument not available');
        }
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

        if (is_object($instrument)) {
            $instrument_component = InstrumentComponent::query()
                ->where('instrument_id', '=', $instrument->id)
                ->where('type', '=', 'main')
                ->get();
            if (count($instrument_component) > 0) {
                foreach ($instrument_component as $item) {
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
            } else {
                return $this->sendError('Failed', 'Instrument not available');
            }
        }
    }


}
