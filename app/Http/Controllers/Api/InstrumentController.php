<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalDocumentResource;
use App\Models\Instrument;
use App\Models\InstrumentComponent;
use App\Models\ProposalDocument;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Validator;
class InstrumentController extends BaseController
{
    public function addnew()
    {
        $data['is_aktif'] = [
            ['tidak_aktif' => 'Tidak Aktif'],
            ['aktif' => 'Aktif']
        ];
        return $this->sendResponse($data, 'Success');
    }
    public function store(Request $request)
    {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            'category' => 'required',
            'periode' => 'required',
            'file_path' => 'nullable',
            'file_name' => 'nullable',
            'file_type' => 'nullable',
            'is_active' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        if ($request->file()) {
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getMimeType(); //getClientMimeType();
            $file_path = $request->file('file')->store('/assets');
            $data = [
                'category' => $input['category'],
                'periode' => $input['periode'],
                'file_path' => $file_path,
                'file_name' => $file_name,
                'file_type' => $file_type,
                'is_active' => $input['is_active']
            ];
        } else {
            $data = [
                'category' => $input['category'],
                'periode' => $input['periode'],
                'is_active' => $input['is_active']
            ];
        }

        $instrument = Instrument::create($data);
        return $this->sendResponse($instrument, 'Instrument Created', $instrument->count);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            'category' => 'required',
            'periode' => 'required',
            'file_path' => 'nullable',
            'file_name' => 'nullable',
            'file_type' => 'nullable',
            'is_active' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $instrument = Instrument::find($id);
        if ($request->file()) {
            $file_name = $request->file('file')->getClientOriginalName();
            $file_type = $request->file('file')->getMimeType(); //getClientMimeType();
            $file_path = $request->file('file')->store($id);
            if (is_object($instrument)) {
                $instrument->category = $input['category'];
                $instrument->periode = $input['periode'];
                $instrument->file_path = $file_path;
                $instrument->file_name = $file_name;
                $instrument->file_type = $file_type;
                $instrument->is_active = $input['is_active'];
            }

        } else {
            if (is_object($instrument)) {
                $instrument->category = $input['category'];
                $instrument->periode = $input['periode'];
                /*$instrument->file_path = $file_path;
                $instrument->file_name = $file_name;
                $instrument->file_type = $file_type;*/
                $instrument->is_active = $input['is_active'];
            }
        }
        $instrument->save();

        return $this->sendResponse($instrument, 'Instrument Updated', $instrument->count);
    }

    public function destroy(Instrument $model)
    {
        //delete component
        //delete aspect
        //delete aspect point
        $model->delete();
        return $this->sendResponse([], 'Instrument Deleted!', $model->count());
    }

    public function index()
    {
        $instruments = Instrument::all();
        return $this->sendResponse($instruments, 'Success', $instruments->count());
    }

    public function edit($id)
    {
        $instrument = Instrument::find($id);
        if (is_object($instrument)) {
            return $this->sendResponse($instrument, "Success", 1);
        } else {

        }
    }

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
            $activeWorksheet->getColumnDimension('M')->setWidth(8);
            $activeWorksheet->getStyle('M')->getAlignment()->setWrapText(true);
            $activeWorksheet->getColumnDimension('N')->setWidth(8);
            $activeWorksheet->getStyle('N')->getAlignment()->setWrapText(true);
            $activeWorksheet->setCellValue('A1', $params);
            $activeWorksheet->setCellValue('B1', 'Instrument-' . $instrument->category);
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
            $activeWorksheet->setCellValue('I2', 'Nilai Asesor');
            $activeWorksheet->setCellValue('J2', 'Keterangan');
            $activeWorksheet->setCellValue('K2', 'Pleno');
            $activeWorksheet->setCellValue('L2', 'Banding');

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

            $styleMainComponent = [
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
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    //'rotation' => 90,
                    'startColor' => [
                        'argb' => 'FFA0A0A0',
                    ],
                    /*'endColor' => [
                        'argb' => 'FFFFFFFF',
                    ],*/
                ],
            ];

            $activeWorksheet->getStyle('A2:L2')->applyFromArray($styleArray);

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
                $activeWorksheet->setCellValue('M' . strval($component_row), $component->id);
                $activeWorksheet->getStyle('A' . strval($component_row) . ':L' . strval($component_row))
                    ->applyFromArray($styleMainComponent);
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
                    $isMultiAspect = false;
                    if ($ins_sub_sub_com->count() == 0) {
                        $ins_sub_sub_com = DB::table('instrument_components')
                            ->select('*')
                            ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                            ->where('instrument_aspects.instrument_component_id', '=', $sub_com->id)
                            ->where('instrument_aspects.instrument_id', '=', $params)
                            ->where('instrument_aspects.parent_id', '=', null)
                            ->get();
                        $isMultiAspect = true;
                    }
                    $sub_component_row++;
                    $sub_sub_component_row = $sub_component_row;
                    foreach ($ins_sub_sub_com as $sub_sub_com) {
                        if($isMultiAspect){
                            $type = $sub_sub_com->aspect;
                        }else{
                            $type = 'choice';
                        }
                        

                        $activeWorksheet->setCellValue('A' . strval($sub_sub_component_row + 1), $butir . '.' . $sub_butir . '.' . $sub_sub_butir);
                        $activeWorksheet->setCellValue('B' . strval($sub_sub_component_row + 1), '--' . $sub_sub_com->name);
                        $sub_sub_component_row++;
                        $sub_sub_butir++;
                        if ($type == 'multi_aspect') {//khusus multi aspect =============================================
                            $ins_aspect = DB::table('instrument_components')
                                ->select(['instrument_components.*', 'instrument_aspects.id as aspectable_id', 'instrument_aspects.aspect'])
                                ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                                ->where('instrument_aspects.parent_id', '=', $sub_sub_com->id)
                                ->where('instrument_aspects.instrument_component_id', '=', $sub_sub_com->instrument_component_id)
                                ->where('instrument_aspects.instrument_id', '=', $params)
                                ->get();
                            $ins_com_aspect = $sub_sub_component_row;
                            $butir_aspect = 1;
                            
                            $temp_arr_asp_point = []; $idx_asp_point = 0;
                            foreach ($ins_aspect as $aspect) {
                                $temp_arr_asp_point[$idx_asp_point] = $aspect;
                                $idx_asp_point++;
                            }
                            $obj_0 = $temp_arr_asp_point[0];
                            $obj_1 = $temp_arr_asp_point[1];
                            $obj_2 = $temp_arr_asp_point[2];
                            $obj_3 = $temp_arr_asp_point[3];
                            $obj_4 = $temp_arr_asp_point[4];
                            $activeWorksheet->setCellValue('C' . strval($ins_com_aspect + 1), $obj_0->aspect);
                            $activeWorksheet->getStyle('C' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('D' . strval($ins_com_aspect + 1), $obj_1->aspect);
                            $activeWorksheet->getStyle('D' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('E' . strval($ins_com_aspect + 1), $obj_2->aspect);
                            $activeWorksheet->getStyle('E' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('F' . strval($ins_com_aspect + 1), $obj_3->aspect);
                            $activeWorksheet->getStyle('F' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->setCellValue('G' . strval($ins_com_aspect + 1), $obj_4->aspect);
                            $activeWorksheet->getStyle('G' . strval($ins_com_aspect + 1))->getAlignment()->setWrapText(true);
                            $activeWorksheet->getStyle('H' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('f8fc03');
                            $activeWorksheet->getStyle('I' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('dafcb1');
                            $activeWorksheet->getStyle('J' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('dafcb1');
                            $activeWorksheet->getStyle('K' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('dafcb1');
                            $activeWorksheet->getStyle('L' . strval($ins_com_aspect + 1))->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('dafcb1');
                            //$activeWorksheet->getStyle('H' . strval($ins_com_aspect + 1))
                            //->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $activeWorksheet->setCellValue('M' . strval($ins_com_aspect + 1), $aspect->id);
                            $activeWorksheet->setCellValue('N' . strval($ins_com_aspect + 1), $aspect->aspectable_id);
                            //$activeWorksheet->setCellValue('K' . strval($ins_com_aspect + 1), $aspect->instrument_aspect_point_id);
                            //}
                            $butir_aspect++;
                            $ins_com_aspect++;
                        } else {//choice =====================================================
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
                                $activeWorksheet->getStyle('I' . strval($ins_com_aspect + 1))->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('dafcb1');
                                $activeWorksheet->getStyle('J' . strval($ins_com_aspect + 1))->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('dafcb1');
                                $activeWorksheet->getStyle('K' . strval($ins_com_aspect + 1))->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('dafcb1');
                                $activeWorksheet->getStyle('L' . strval($ins_com_aspect + 1))->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('dafcb1');
                                //$activeWorksheet->getStyle('H' . strval($ins_com_aspect + 1))
                                //->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                                $activeWorksheet->setCellValue('M' . strval($ins_com_aspect + 1), $aspect->id);
                                $activeWorksheet->setCellValue('N' . strval($ins_com_aspect + 1), $aspect->aspectable_id);
                                //$activeWorksheet->setCellValue('K' . strval($ins_com_aspect + 1), $aspect->instrument_aspect_point_id);
                                //}
                                $butir_aspect++;
                                $ins_com_aspect++;
                            }
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
            $activeWorksheet->getStyle('A2:L' . strval($component_row))->applyFromArray($styleBorder);
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

    public function getDocumentSK($id)
    {
        $file_sk = Instrument::find($id);
        if (is_object($file_sk)) {
            $file_path = $file_sk->file_path;
            $file_name = $file_sk->file_name;
            $file_type = $file_sk->file_type;
            try {
                $file_content = Storage::get($file_path);
                return response($file_content, 200)
                    ->header('Content-Type', $file_type) // Set Content-Type header
                    ->header('Content-Disposition', 'attachment; filename="' . $file_name . '"');
                //return Storage::download($file_path, $accre_file->file_name);
            } catch (FileNotFoundException $e) {
                return $this->sendError('File not Found', 'File not available in hard drive!');
            }

        } else {
            return $this->sendError('Record not Found', 'Record not available in database!');
        }
    }


}
