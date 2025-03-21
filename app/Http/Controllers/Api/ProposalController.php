<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AccreditationProposalResource;
use App\Models\AccreditationContent;
use App\Models\InstitutionRequest;
use ArrayObject;
use File;
use Illuminate\Http\Request;
use App\Models\AccreditationProposal;
use App\Models\ProposalState;
use App\Models\AccreditationProposalFiles;
use App\Models\ProposalDocument;
use App\Models\Instrument;
use App\Models\Province;
use App\Models\Region;
use App\Models\ProvinceRegion;
use App\Models\Evaluation;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalController extends BaseController {

    function __construct() {
        
    }

    function __destruct() {
        
    }

    /**
     * @param $model
     * 
     * @param model
     */
    public function destroy(AccreditationProposal $model) {
        $proposal_files = AccreditationProposalFiles::find()->where('accreditation_proposal_id', '=', $model->id)->get();
        //delete files
        foreach ($proposal_files as $file) {
            $file->delete();
        }
        $model->delete();
        return $this->sendResponse([], 'Proposal Deleted!', $model->count());
    }

    public function index() {
        $accreditation = DB::table('accreditation_proposals')
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                ->select(['institution_requests.*',
                    'accreditation_proposals.proposal_date',
                    'accreditation_proposals.predicate',
                    'accreditation_proposals.accredited_at',
                    'accreditation_proposals.type',
                    'proposal_states.state_name'])
                ->where('accreditation_proposals.proposal_state_id', '>', '0')
                ->get();
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'Data Usulan Akreditasi');
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A1')->getFont()->setSize(16);
        $activeWorksheet->setCellValue('A2', 'ID');
        $activeWorksheet->setCellValue('B2', 'Tgl Pengajuan');
        $activeWorksheet->setCellValue('C2', 'Nama Institusi');
        $activeWorksheet->setCellValue('D2', 'Status');
        $activeWorksheet->setCellValue('E2', 'Predikat');
        $activeWorksheet->setCellValue('F2', 'Tgl Akreditasi');
        $activeWorksheet->setCellValue('G2', 'Type');
        $activeWorksheet->setCellValue('H2', 'Category');
        $activeWorksheet->setCellValue('I2', 'Propinsi');
        $activeWorksheet->setCellValue('J2', 'Kabupaten');
        $activeWorksheet->setCellValue('K2', 'Kecamatan');
        $activeWorksheet->setCellValue('L2', 'Desa');
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
        $activeWorksheet->getStyle('A2:L2')->applyFromArray($styleArray);
        $row = 3;
        foreach ($accreditation as $accre) {
            $activeWorksheet->setCellValue('A' . strval($row), $accre->accreditation_proposal_id);
            $activeWorksheet->setCellValue('B' . strval($row), $accre->proposal_date);
            $activeWorksheet->setCellValue('C' . strval($row), $accre->library_name);
            $activeWorksheet->setCellValue('D' . strval($row), $accre->state_name);
            $activeWorksheet->setCellValue('E' . strval($row), $accre->predicate);
            $activeWorksheet->setCellValue('F' . strval($row), $accre->accredited_at);
            $activeWorksheet->setCellValue('G' . strval($row), $accre->type);
            $activeWorksheet->setCellValue('H' . strval($row), $accre->category);
            $activeWorksheet->setCellValue('I' . strval($row), $accre->province_name);
            $activeWorksheet->setCellValue('J' . strval($row), $accre->city_name);
            $activeWorksheet->setCellValue('K' . strval($row), $accre->subdistrict_name);
            $activeWorksheet->setCellValue('L' . strval($row), $accre->village_name);
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
                    $writer->save('php://output');
                });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="data-usulan-akreditasi.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
        //return $this->sendResponse(new AccreditationProposalResource($accreditation), 'Success', $accreditation->count());
    }

    public function list(Request $request) {//with filter
        $query = AccreditationProposal::query()
                ->join('institution_requests', 'accreditation_proposals.id', '=', 'institution_requests.accreditation_proposal_id')
                ->join('proposal_states', 'accreditation_proposals.proposal_state_id', '=', 'proposal_states.id')
                //->join('provinces', 'institution_requests.province_id', '=', 'provinces.id')
                //->join('cities', 'institution_requests.city_id', '=', 'cities.id')
                //->join('subdistricts', 'institution_requests.subdistrict_id', '=', 'subdistricts.id')
                //->join('villages', 'institution_requests.village_id', '=', 'villages.id')
                ->select([
                    'accreditation_proposals.*',
                    'proposal_states.state_name',
                    'institution_requests.category',
                    'library_name',
                    'npp',
                    'agency_name',
                    'institution_head_name',
                    'email',
                    'telephone_number',
                    'province_name as province',
                    'city_name as city',
                    'subdistrict_name as subdistrict',
                    'village_name as village'
        ]);
        if ($s = $request->input(key: 'search')) {//filter berdasarkan name            
            $query->where('institution_requests.library_name', 'like', "%{$s}%");
        }
        if ($s = $request->input(key: 'province_id')) {//filter berdasarkan name            
            $query->where('institution_requests.province_id', '=', "{$s}");
        }
        if ($s = $request->input(key: 'city_id')) {//filter berdasarkan name            
            $query->where('institution_requests.city_id', '=', "{$s}");
        }
        if ($s = $request->input(key: 'subdistrict_id')) {//filter berdasarkan name            
            $query->where('institution_requests.subdistrict_id', '=', "{$s}");
        }
        if ($s = $request->input(key: 'state_name')) {//filter berdasarkan name            
            $query->where('proposal_states.state_name', '=', "{$s}");
        }
        $query->orderBy('proposal_date', 'desc');
        $perPage = $request->input(key: 'pageSize', default: 10);
        $page = $request->input(key: 'page', default: 1);
        $total = $query->count();
        $response = $query->offset(value: ($page - 1) * $perPage)
                ->limit($perPage)
                ->paginate();
        return $this->sendResponse($response, "Success", $total);
    }

    /**
     * @param $id
     * 
     * @param id
     */
    public function show($id) {
        $accreditation = AccreditationProposal::query()
                ->where(['id' => $id])
                ->with('proposalState')
                //->with('institutionRequest')            
                ->get();
        $institution_request = InstitutionRequest::query()
                ->where(['accreditation_proposal_id' => $id])
                ->with('province')
                ->with('city')
                ->with('subDistrict')
                ->with('village')
                ->get();
        $accre_files = AccreditationProposalFiles::query()
                ->where(['accreditation_proposal_id' => $id])
                ->with('proposalDocument')
                ->get();
        //$accre_contents = AccreditationContent::query()
        /* $accre_contents = DB::table('accreditation_contents')
          ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
          ->select(
          'instrument_components.name',
          'instrument_components.weight',
          DB::raw('SUM(value) as nilai_sa'),
          DB::raw('(SUM(value) * instrument_components.weight) / 100 as total_nilai_sa'),
          DB::raw('SUM(0) as nilai_evaluasi'),
          DB::raw('SUM(0) as total_nilai_evaluasi'),
          'main_component_id'
          )
          ->where('accreditation_proposal_id', $id)
          ->groupBy('main_component_id', 'instrument_components.name', 'instrument_components.weight')
          ->get(); */
        /* $accre_contents = DB::table('evaluation_contents')
          ->join('accreditation_contents', 'evaluation_contents.accreditation_content_id', '=', 'accreditation_contents.id')
          ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
          ->select(
          'instrument_components.name',
          'instrument_components.weight',
          DB::raw('SUM(accreditation_contents.value) as nilai_sa'),
          DB::raw('(SUM(accreditation_contents.value) * instrument_components.weight) / 100 as total_nilai_sa'),
          DB::raw('SUM(evaluation_contents.value) as nilai_evaluasi'),
          DB::raw('(SUM(evaluation_contents.value) * instrument_components.weight) / 100 as total_nilai_evaluasi'),
          'accreditation_contents.main_component_id'
          )
          ->where('accreditation_proposal_id', $id)
          ->groupBy('accreditation_contents.main_component_id', 'instrument_components.name', 'instrument_components.weight')
          ->get(); */

        /* $accre_contents = DB::table('accreditation_contents')
          ->select(
          'instrument_components.name',
          'instrument_components.weight',
          DB::raw('SUM(accreditation_contents.value) as nilai_sa'),
          DB::raw('(SUM(accreditation_contents.value) * instrument_components.weight) / 100 as total_nilai_sa'),
          DB::raw('SUM(evaluation_contents.value) as nilai_evaluasi'),
          DB::raw('(SUM(evaluation_contents.value) * instrument_components.weight) / 100 as total_nilai_evaluasi'),
          'accreditation_contents.main_component_id'
          )
          ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
          ->leftJoin('evaluation_contents', 'accreditation_contents.id', '=', 'evaluation_contents.accreditation_content_id')
          ->where('accreditation_contents.accreditation_proposal_id', $id)
          ->groupBy('accreditation_contents.main_component_id', 'instrument_components.name', 'instrument_components.weight')
          ->get(); */


        //(NILAI SA / (JUMLAH SOAL * 5)) * BOBOT
        $accre_contents = DB::table('accreditation_contents')
                ->select([
                    'instrument_components.name',
                    'instrument_components.weight',
                    DB::raw('SUM(accreditation_contents.value) AS nilai_sa'),
                    DB::raw('COUNT(accreditation_contents.value) AS jumlah_soal'),
                    DB::raw('(SUM(accreditation_contents.value) / (COUNT(accreditation_contents.value) * 5)) * instrument_components.weight AS total_nilai_sa'),
                    DB::raw('SUM(merged_evaluation_contents.value) AS nilai_evaluasi'),
                    DB::raw('(SUM(merged_evaluation_contents.value) / (COUNT(merged_evaluation_contents.value) * 5)) * instrument_components.weight AS total_nilai_evaluasi'),
                    'accreditation_contents.main_component_id',
                    'evaluation_recomendations.content as recommendation'
                ])
                ->join('instrument_components', 'accreditation_contents.main_component_id', '=', 'instrument_components.id')
                ->leftJoin('merged_evaluation_contents', 'accreditation_contents.id', '=', 'merged_evaluation_contents.accreditation_content_id')
                ->leftJoin('evaluation_recomendations', 'merged_evaluation_contents.evaluation_id', '=', 'evaluation_recomendations.evaluation_id')
                ->where('accreditation_contents.accreditation_proposal_id', $id)
                ->groupBy(
                        'accreditation_contents.main_component_id',
                        'instrument_components.name',
                        'instrument_components.weight'
                )   
                ->get();

        $proposal_states = ProposalState::all();
        $is_valid = [
            ['label' => 'Valid', 'value' => 'valid'],
            ['label' => 'Tidak Valid', 'value' => 'tidak_valid']
        ];
        $certicate_status = [
            ['label' => 'Cetak Sertifikat', 'value' => 'cetak_sertifikat'],
            ['label' => 'Dikirim', 'value' => 'dikirim'],
            ['label' => 'Ditandatangani', 'value' => 'ditandatangani'],
            ['label' => 'Terakreditasi', 'value' => 'terakreditasi'],
        ];

        $data['accreditation_proposal'] = $accreditation;
        $data['institution_request'] = $institution_request;
        $data['accreditation_proposal_files'] = $accre_files;
        $data['proposal_states'] = $proposal_states;
        $data['certificate_status'] = $certicate_status;
        $data['is_valid'] = $is_valid;
        $data['accreditation_evaluation'] = $accre_contents;

        if (is_null($accreditation)) {
            return $this->sendError('Proposal not found!');
        }
        return $this->sendResponse($data, 'Proposal Available', $accreditation->count());
    }

    /**
     * @param $request
     * 
     * @param request
     */
    public function store(Request $request) {
        $input = $request->all();
        //validating---------------------------
        $validator = Validator::make($input, [
            'institution_id' => 'required',
            'proposal_date' => 'required',
            'proposal_state_id' => 'required',
            'finish_date' => 'nullable',
            'type' => 'required',
            'notes' => 'nullable',
            'accredited_at' => 'nullable',
            'predicate' => 'nullable',
            'certificate_status' => 'nullable',
            'certificate_expires_at' => 'nullable',
            'pleno_date' => 'nullable',
            'certificate_file' => 'nullable',
            'recommendation_file' => 'nullable',
            'is_valid' => 'required',
            'instrument_id' => 'required',
            'category' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error!', $validator->errors());
        }
        $proposal = AccreditationProposal::create($input);
        return $this->sendResponse(new AccreditationProposalResource($proposal), 'Proposal Created', $proposal->count);
    }

    /**
     * @param $request
     * @param $model
     * 
     * @param request
     * @param model
     */
    public function update(Request $request, $id) {
        $input = $request->all();
        $user_role = '';
        $perpus_id = '';
        if ($request->hasHeader('Access-User')) {
            $temp_request_header = $request->header('Access-User');
            $request_header = str_replace('\"', '"', $temp_request_header);
            $request_header = json_decode($request_header, true);
            $userid = $request_header['id'];
            $roles = $request_header['roles'];
            $perpus_id = $request_header['perpus_id'];
            foreach ($roles as $role) {
                if ($role['name'] == 'ADMIN PERPUSTAKAAN') {
                    $user_role = $role['name'];
                }
            }
        }
        if ($user_role !== 'ADMIN PERPUSTAKAAN') {
            $validator = Validator::make($input, [
                'institution_id' => 'required',
                'proposal_date' => 'required',
                'proposal_state_id' => 'required',
                'finish_date' => 'nullable',
                'type' => 'required',
                'notes' => 'nullable',
                'accredited_at' => 'nullable',
                'predicate' => 'nullable',
                'certificate_status' => 'nullable',
                'certificate_expires_at' => 'nullable',
                'pleno_date' => 'nullable',
                'certificate_file' => 'nullable',
                'recommendation_file' => 'nullable',
                'is_valid' => 'required',
                'instrument_id' => 'required',
                'category' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error!', $validator->errors());
            }
            $model = AccreditationProposal::find($id);
            if (is_object($model)) {
                $model->institution_id = $input['institution_id'];
                $model->proposal_date = $input['proposal_date'];
                /*if ($input['is_valid'] == 'valid') {
                    $model->proposal_state_id = 2;
                } else {*/
                    $model->proposal_state_id = $input['proposal_state_id'];
                //}
                $model->finish_date = $input['finish_date'];
                $model->type = $input['type'];
                $model->notes = $input['notes'];
                $model->accredited_at = $input['accredited_at'];
                $model->predicate = $input['predicate'];
                $model->certificate_status = $input['certificate_status'];
                $model->certificate_expires_at = $input['certificate_expires_at'];
                $model->pleno_date = $input['pleno_date'];
                $model->is_valid = $input['is_valid'];
                $model->instrument_id = $input['instrument_id'];
                //$model->category = $input['category'];
                //certificate-file
                if ($request->file()) {
                    $directory = 'certifications/' . $model->id;
                    $file_certificate = $request->file('certificate_file')->store($directory);
                    $model->certificate_file = $file_certificate; //Storage::url($file_certificate);
                    $directory = 'recommendations/' . $model->id;
                    $file_recommendation = $request->file('recommendation_file')->store($directory);
                    $model->recommendation_file = $file_recommendation; //Storage::url($file_recommendation);
                }

                $model->save();

                $ins_request = InstitutionRequest::where('accreditation_proposal_id', '=', $id)->first();
                $ins_request->status = $input['is_valid'];
                $ins_request->save();

                //update data akreditasi ke perpustakaan
                //ambil skor evaluasi
                $evaluation = Evaluation::where('accreditation_proposal_id', '=', $id)->get();
                $skor = 0;
                if (count($evaluation) > 0) {
                    foreach ($evaluation as $eva) {
                        $skor = $skor + $eva->skor;
                    }
                }
                $url = "http://103.23.199.161/api/perpustakaan/" . $perpus_id;
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                        //CURLOPT_USERAGENT => $userAgent,
                ]);

                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    "cache-control: no-cache",
                    "Access-User: " . $temp_request_header
                ]);

                $response = json_decode(curl_exec($curl));
                $error = curl_error($curl);
                $perpustakaan = null;
                if (is_object($response)) {
                    $perpustakaan = $response->data;
                    $perpustakaan->tanggal_akreditasi = $input['accredited_at'];
                    $perpustakaan->nilai_akreditasi = $input['predicate'];

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "PATCH",
                        CURLOPT_POSTFIELDS => json_decode($perpustakaan),
                        //CURLOPT_USERAGENT => $userAgent,
                    ]);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, [
                        "cache-control: no-cache",
                        "Access-User: " . $temp_request_header
                    ]);
                    $response = json_decode(curl_exec($curl));
                    $error = curl_error($curl);
                }
                $return = ['accreditation_proposal' => $model, 'perpustakaan' => $perpustakaan];
            } else {
                $this->sendError('Error', 'Object not found!');
            }
        } else {
            $return = [];
        }
        return $this->sendResponse(new AccreditationProposalResource($return), 'Accreditation Updated!', $model->count());
    }

    public function showFile($id, $file) {
        $accre_file = AccreditationProposal::find($id);

        /* if($file == 'certificate_file'){
          $accre_file->where('certificate_file','=', $file)->first();
          }elseif($file == 'recommendation_file'){
          $accre_file->where('recommendation_file', '=', $file)->first();
          } */
        //return json_encode($accre_file);
        if (is_object($accre_file)) {
            if ($file == 'certificate_file') {
                $file_path = $accre_file->certificate_file;
                $file_name = 'certificate_file.pdf';
            } elseif ($file == 'recommendation_file') {
                $file_path = $accre_file->recommendation_file;
                $file_name = 'recommendation_file.pdf';
            }
            try {
                $file_content = Storage::get($file_path);
                return response($file_content, 200)
                                ->header('Content-Type', 'application/pdf') // Set Content-Type header
                                ->header('Content-Disposition', 'attachment; filename="' . $file_name . '"');
                //return Storage::download($file_path, $accre_file->file_name);
            } catch (FileNotFoundException $e) {
                return $this->sendError('File not Found', 'File not available in hard drive!');
            }
        } else {
            return $this->sendError('Record not Found', 'Record not available in database!');
        }
    }

    public function showFiles($id, Request $request) {
        if ($request->hasHeader('Access-User')) {
            $accre_file = AccreditationProposalFiles::find($id);
            if (is_object($accre_file)) {
                $file_path = $accre_file->file_path;
                $file_name = $accre_file->file_name;
                $file_type = $accre_file->file_type;
                try {
                    $file_content = Storage::get($file_path);
                    return response($file_content, 200)
                                    ->header('Content-Type', $file_type) // Set Content-Type header
                                    ->header('Access-Control-Expose-Headers', 'Content-Disposition, Content-Type')
                                    ->header('Content-Disposition', 'attachment; filename="' . $file_name . '"');
                    //return Storage::download($file_path, $accre_file->file_name);
                } catch (FileNotFoundException $e) {
                    return $this->sendError('File not Found', 'File not available in hard drive!');
                }
            } else {
                return $this->sendError('Record not Found', 'Record not available in database!');
            }
        } else {
            return $this->sendError('Error', 'Authorization Failed!');
        }
    }
}
