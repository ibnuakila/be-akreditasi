<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\AccreditationProposal;
use DB;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    
    public function index()
    {
        $count_usulan_accre = AccreditationProposal::query()
            ->where('proposal_state_id', '=', 1)->count();

        $count_dinilai = AccreditationProposal::query()
            ->where('proposal_state_id', '=', 2)->count();

        $count_ditinjau = AccreditationProposal::query()
            ->where('proposal_state_id', '=', 3)->count();

        $count_terakreditasi = AccreditationProposal::query()
            ->where('proposal_state_id', '=', 4)->count();

        $count_akreditasi_a = AccreditationProposal::query()
            ->where('predicate', '=', 'A')->count();
            $count_akreditasi_b = AccreditationProposal::query()
            ->where('predicate', '=', 'B')->count();
            $count_akreditasi_c = AccreditationProposal::query()
            ->where('predicate', '=', 'C')->count();
            $count_akreditasi_null = AccreditationProposal::query()
            ->where('predicate', '=',  null)->count();
            
        $data['usulan_akreditasi_baru'] = $count_usulan_accre;
        $data['usulan_akreditasi_dinilai'] = $count_dinilai;
        $data['usulan_akreditasi_ditinjau'] = $count_ditinjau;
        $data['usulan_akreditasi_terakreditasi'] = $count_terakreditasi;
        $data['total_akreditasi_a'] = $count_akreditasi_a;
        $data['total_akreditasi_b'] = $count_akreditasi_b;
        $data['total_akreditasi_c'] = $count_akreditasi_c;
        $data['total_akreditasi_null'] = $count_akreditasi_null;

        return $this->sendResponse($data, 'Success', count($data));

    }
}
