<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AccreditationFeedback;
use App\Models\MasterFeedback;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccreditationFeedbackController extends BaseController 
{
    public function addNew()
    {
        $response = MasterFeedback::query()->select('*')->get();
        return $this->sendResponse($response, "Success", count($response));	
    }
    
	public function destroy(AccreditationFeedback $model)
    {
        $model->delete();
		return $this->sendResponse([], 'Feedback Deleted!', $model->count());
    }

	public function index(Request $request)
    {
        $query = AccreditationFeedback::query()
        ->join('master_feedbacks','accreditation_feedbacks.master_feedback_id','=','master_feedbacks.id')
        ->join('institution_requests','accreditation_feedbacks.accreditation_proposal_id','=','institution_requests.accreditation_proposal_id');
        
        //->with('masterFeedback')
        //->with('institutionRequest');
            if($s = $request->input(key:'search')){//filter berdasarkan name 
                $query->where('library_name', 'like', "%{$s}%");			
            }
        $perPage = 15;
		$page = $request->input(key:'page', default:1);
		$total = $query->count();
		$response = $query->offset(value:($page - 1) * $perPage)
			->limit($perPage)
				->paginate();
		return $this->sendResponse($response, "Success", $total);	
    }

	
	public function show($id)
    {
        $feedback = AccreditationFeedback::find($id);
		if(!is_object($feedback)){
			return $this->sendError('Feedback not found!');
		}
		return $this->sendResponse(($feedback), 'Feedback Available', $feedback->count());
    }

	
	public function store(Request $request){
        $input = $request->all();
		//validating---------------------------
		$validator = Validator::make($input, [
			'accreditation_proposal_id' => 'required',
            'accreditation_date' => 'nullable',
            'feedback' => 'required'            
		]);
        $feedback = json_decode($request['feedback']);
        foreach($feedback->data as $row){
            $data = [
                'accreditation_proposal_id' => $input['accreditation_proposal_id'],
                'accreditation_date' => $input['accreditation_date'],
                'answer' => $row->answer,
                'note' => $row->note,
                'master_feedback_id' => $row->master_feedback_id,
            ];
            $accre_feedback = AccreditationFeedback::create($data);
        }
		if($validator->fails()){
			return $this->sendError('Validation Error!', $validator->errors());
		}
		//
		return $this->sendResponse(($accre_feedback), 'Feedback Created', $accre_feedback->count());
    }

	
	public function update(Request $request, AccreditationFeedback $model)
    {
        $input = $request->all();

		$validator = Validator::make($input, [
			'answer' => 'required'
		]);

		if($validator->fails()){
            return $this->sendError('Validation Error!', $validator->errors());
        }
		$model->answer = $input['answer'];	        
		$model->update();

		return $this->sendResponse(($model), 'Feedback Updated!', $model->count());
    }

    public function export(Request $request)
    {
        $feedbacks = AccreditationFeedback::query()
        ->join('master_feedbacks','accreditation_feedbacks.master_feedback_id','=','master_feedbacks.id')
        ->join('institution_requests','accreditation_feedbacks.accreditation_proposal_id','=','institution_requests.accreditation_proposal_id')
        ->get();
        
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'Data Feedback');
        $activeWorksheet->setCellValue('A2', 'No');
        $activeWorksheet->setCellValue('B2', 'Nama Asesi');
        $activeWorksheet->setCellValue('C2', 'Feedback');
        $activeWorksheet->setCellValue('D2', 'Jawaban');
        $activeWorksheet->setCellValue('E2', 'Catatan');
        $activeWorksheet->setCellValue('F2', 'Tgl Akreditasi');

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
        $activeWorksheet->getStyle('A2:F2')->applyFromArray($styleArray);
        $row = 3; $i =1;
        foreach ($feedbacks as $accre) {
            $activeWorksheet->setCellValue('A' . strval($row), $i);
            $activeWorksheet->setCellValue('B' . strval($row), $accre->library_name);
            $activeWorksheet->setCellValue('C' . strval($row), $accre->feedback);
            $activeWorksheet->setCellValue('D' . strval($row), $accre->answer);
            $activeWorksheet->setCellValue('E' . strval($row), $accre->note);
            $activeWorksheet->setCellValue('F' . strval($row), $accre->accreditation_date);            
            $row++; $i++;
        }
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
                    $writer->save('php://output');
                });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="data-feedback-akreditasi.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
