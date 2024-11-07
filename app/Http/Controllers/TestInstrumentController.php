<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestInstrumentController extends Controller
{
    //
    public function index($category){
        $instrument = Instrument::find($category);
        echo '<table>';
        $ins_com = DB::table('instrument_components')//query main component
                ->select('*')
                ->where('type', '=', 'main')
                ->where('instrument_id', '=', $category)
                ->get();
        $data['instrument_component_main'] = $ins_com;
        $idx_main = 1;
                foreach ($ins_com as $component) {//main component ======================================================
                    echo '<tr>';
                    echo '<td>'.$idx_main.'</td>';
                    echo '<td>'.$component->id.'</td>';
                    echo '<td><b>'.$component->name.'</b></td>';

                    $ins_sub_com = DB::table('instrument_components')//query sub_1 component
                    ->select('*')
                    ->where('type', '=', 'sub_1')
                    ->where('parent_id', '=', $component->id)
                    ->where('instrument_id', '=', $category)
                    ->get();
                    $data['instrument_component_sub_1'] = $ins_sub_com;
                    $idx_sub_com = 1;
                    foreach ($ins_sub_com as $sub_component) {//sub_1 component =========================================
                        echo '<tr>';
                        echo '<td>'.$idx_main.'.'.$idx_sub_com.'</td>';
                        echo '<td>'.$sub_component->id.'</td>';
                        echo '<td>'.$sub_component->name.'</td>';
                        echo '</tr>';
                        $ins_sub_sub_com = DB::table('instrument_components')//query sub_2 component
                        ->select('*')
                        ->where('type', '=', 'sub_2')
                        ->where('parent_id', '=', $sub_component->id)
                        ->where('instrument_id', '=', $category)
                        ->get();

                        if($ins_sub_sub_com->count() == 0){
                            echo '<tr><td></td><td><b>'.$sub_component->id.'</b></td></tr>';

                        }else{
                            
                        }
                        $idx_sub_sub_com = 1;
                        foreach($ins_sub_sub_com as $sub_sub_component) {//loop sub_2 component =============================
                            echo '<tr>';
                            echo '<td>'.$idx_main.'.'.$idx_sub_com.'.'.$idx_sub_sub_com.'</td>';
                            echo '<td>'.$sub_sub_component->id.'</td>';
                            echo '<td>'."&ensp;".$sub_sub_component->name.'</td>';                            
                            echo '</tr>';

                            $ins_sub_sub_com_aspect = DB::table('instrument_components')
                                ->select(['instrument_components.*', 'instrument_aspects.id as aspectable_id', 'instrument_aspects.aspect'])
                                ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                                //->where('instrument_components.type', '=', 'sub_2')
                                ->where('instrument_component_id', '=', $sub_sub_component->id)
                                ->where('instrument_components.instrument_id', '=', $category)
                                ->get();
                            $idx_sub_sub_com_aspect = 1;
                            if($ins_sub_sub_com_aspect->count() > 0) {
                                foreach($ins_sub_sub_com_aspect as $sub_sub_com_aspect){//aspect ============================
                                    echo '<tr>';
                                    echo '<td>'.$idx_sub_sub_com_aspect.'</td>';
                                    echo '<td>'.$sub_sub_com_aspect->id.'</td>';
                                    echo '<td>'."&ensp;".$sub_sub_com_aspect->aspect.'</td>';                                
                                    echo '</tr>';
                                    $idx_sub_sub_com_aspect++;
                                }
                            }else{
                                $ins_sub_sub_com_aspect = DB::table('instrument_components')
                                ->select(['instrument_components.*', 'instrument_aspects.id as aspectable_id', 'instrument_aspects.aspect'])
                                ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                                //->where('instrument_components.type', '=', 'sub_2')
                                ->where('instrument_component_id', '=', $sub_sub_component->id)
                                ->where('instrument_components.instrument_id', '=', $category)
                                ->get();

                            }

                            $idx_sub_sub_com++;
                        }

                        $idx_sub_com++;
                    }
                    echo '</tr>';
                    $idx_main++;
                }
        echo '</table>';
    }
}
