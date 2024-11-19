<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestInstrumentController extends Controller
{
    //
    public function index($category)
    {
        $instrument = Instrument::find($category);
        echo '<table>';
        $ins_com = DB::table('instrument_components')//query main component
            ->select('*')
            ->where('type', '=', 'main')
            ->where('instrument_id', '=', $category)
            ->get();
        //$data['instrument_component_main'] = $ins_com;
        $idx_main = 1;
        foreach ($ins_com as $component) {//main component ======================================================
            echo '<tr>';
            echo '<td>' . $idx_main . '</td>';
            echo '<td>' . $component->id . '</td>';
            echo '<td><b>' . $component->name . '</b></td>';

            $ins_sub_com = DB::table('instrument_components')//query sub_1 component
                ->select('*')
                ->where('type', '=', 'sub_1')
                ->where('parent_id', '=', $component->id)
                ->where('instrument_id', '=', $category)
                ->get();
            //$data['instrument_component_sub_1'] = $ins_sub_com;
            $idx_sub_com = 1;
            $idx_sub_sub_com_aspect = 1;
            foreach ($ins_sub_com as $sub_component) {//looping sub_1 component =========================================
                echo '<tr>';
                echo '<td>' . $idx_main . '.' . $idx_sub_com . '</td>';
                echo '<td>' . $sub_component->id . '</td>';
                echo '<td>' . $sub_component->name . '</td>';
                echo '</tr>';
                $ins_sub_sub_com = DB::table('instrument_components')//query sub_2 component
                    ->select('*')
                    ->where('type', '=', 'sub_2')
                    ->where('parent_id', '=', $sub_component->id)
                    ->where('instrument_id', '=', $category)
                    ->get();
                $is_multi_aspect = false;
                if ($ins_sub_sub_com->count() == 0) {//khusus multi aspect ==================
                    //ambil data aspects ==================================
                    $ins_aspect = DB::table('instrument_aspects')//query aspect untuk multi-aspect
                        ->where('instrument_id', '=', $category)
                        ->where('instrument_component_id', '=', $sub_component->id)
                        ->get();
                    $idx_sub_sub_com = 1;
                    foreach ($ins_aspect as $row_aspect) {
                        echo '<tr style="background-color:#00FF00">';

                        if (is_null($row_aspect->parent_id)) {
                            echo '<td>' . $idx_sub_sub_com_aspect . '</td>';
                        } else {
                            echo '<td></td>';
                        }
                        echo '<td>' . $row_aspect->id . '</td>';
                        echo '<td>' . "&ensp;" . $row_aspect->aspect . '</td>';

                        //ambil instrument-aspect-points ===================
                        $aspect_points = DB::table('instrument_aspect_points')
                            ->where('instrument_aspect_id', '=', $row_aspect->id)->get();
                        /*foreach ($aspect_points as $row_ap){
                            echo '<td>'.$row_ap->statement.'</td>';
                        }*/
                        $idx_asp_point = 0;
                        foreach ($aspect_points as $row_ap) {
                            $array_asp_points[$idx_asp_point] = $row_ap;
                            $idx_asp_point++;
                        }
                        $obj_0 = $array_asp_points[0];
                        $obj_1 = $array_asp_points[1];
                        $obj_2 = $array_asp_points[2];
                        $obj_3 = $array_asp_points[3];
                        $obj_4 = $array_asp_points[4];
                        //foreach ($aspect_points as $row_ap){
                        echo '<td>' . $obj_0->statement . '</td>';
                        echo '<td>' . $obj_1->statement . '</td>';
                        echo '<td>' . $obj_2->statement . '</td>';
                        echo '<td>' . $obj_3->statement . '</td>';
                        echo '<td>' . $obj_4->statement . '</td>';
                        //}
                        echo '</tr>';
                        if (is_null($row_aspect->parent_id)) {
                            $idx_sub_sub_com_aspect++;
                        }
                        $idx_sub_sub_com++;
                        //$idx_sub_sub_com_aspect++;
                    }
                    $idx_sub_com++;
                    $is_multi_aspect = true;
                } else {//choice ====================================


                    $idx_sub_sub_com = 1;

                    foreach ($ins_sub_sub_com as $sub_sub_component) {//looping sub_2 component =============================
                        echo '<tr>';
                        echo '<td>' . $idx_main . '.' . $idx_sub_com . '.' . $idx_sub_sub_com . '</td>';
                        echo '<td>' . $sub_sub_component->id . '</td>';
                        echo '<td>' . "&ensp;" . $sub_sub_component->name . '</td>';
                        echo '</tr>';
                        //ambil data aspect =============================
                        $instrument_aspect = DB::table('instrument_components')//query aspect
                            ->select('instrument_aspects.*')
                            ->join('instrument_aspects', 'instrument_components.id', '=', 'instrument_aspects.instrument_component_id')
                            ->where('instrument_component_id', '=', $sub_sub_component->id)
                            ->where('instrument_components.instrument_id', '=', $category)
                            ->get();

                        if ($instrument_aspect->count() > 0) {
                            foreach ($instrument_aspect as $row_aspect) {//looping aspect ============================
                                echo '<tr>';
                                if (is_null($row_aspect->parent_id)) {
                                    echo '<td>' . $idx_sub_sub_com_aspect . '</td>';
                                } else {
                                    echo '<td></td>';
                                }
                                echo '<td>' . $row_aspect->id . '</td>';
                                echo '<td>' . "&ensp;" . $row_aspect->aspect . '</td>';
                                //echo '</tr>';
                                if (is_null($row_aspect->parent_id)) {
                                    $idx_sub_sub_com_aspect++;
                                }
                                //ambil instrument-aspect-points ===================
                                $aspect_points = DB::table('instrument_aspect_points')
                                    ->where('instrument_aspect_id', '=', $row_aspect->id)->get();
                                $idx_asp_point = 0;
                                foreach ($aspect_points as $row_ap) {
                                    $array_asp_points[$idx_asp_point] = $row_ap;
                                    $idx_asp_point++;
                                }
                                $obj_0 = $array_asp_points[0];
                                $obj_1 = $array_asp_points[1];
                                $obj_2 = $array_asp_points[2];
                                $obj_3 = $array_asp_points[3];
                                $obj_4 = $array_asp_points[4];
                                //foreach ($aspect_points as $row_ap){
                                echo '<td>' . $obj_0->statement . '</td>';
                                echo '<td>' . $obj_1->statement . '</td>';
                                echo '<td>' . $obj_2->statement . '</td>';
                                echo '<td>' . $obj_3->statement . '</td>';
                                echo '<td>' . $obj_4->statement . '</td>';
                                //}
                                echo '</tr>';
                            }
                        }

                        $idx_sub_sub_com++;
                    }

                    $idx_sub_com++;
                }
            }
            echo '</tr>';
            $idx_main++;
        }
        echo '</table>';
    }

    public function getDetailInstrument($category)
    {
        /*$ins_com = Instrument::query()//query main component
            ->select('*')
            //->where('type', '=', 'main')
            ->where('id', '=', $category)
            ->with('instrumentComponent')
            ->where('instrument_components.type', '=', 'main')
            ->get();*/
        /*$ins_com = Instrument::query()
        ->select('*')
        ->where('id', '=', $category)
        ->whereHas('instrumentComponent', function ($query) {
            $query->where('type', '=', 'main');
        })
        ->with(['instrumentComponent' => function ($query) {
            $query->where('type', '=', 'main');
        }])
        ->get();*/
        /*$ins_com = Instrument::query()
            ->where('id', $category)
            ->with(['instrumentComponent' => function ($query) {
                $query->where('type', 'main')
                ->with('children.children')
                ->with('instrumentAspect'); // Recursively load children
            }])
            ->get();*/

        $instrument = Instrument::query()
            ->where('id', $category)  // Or another condition to filter the Instrument
            ->with([
                'instrumentComponent' => function ($query) use ($category) {
                    $query->where('type', 'main')
                        ->with([
                            'children' => function ($query) use ($category) { // Load child components
                                $query->with([
                                    'children' => function ($query) use ($category) {
                                    $query->with([
                                        'instrumentAspect' => function ($query) use ($category) { // Load aspects of each component
                                            $query->with([
                                                'instrumentAspectPoint' => function ($query) use ($category) {
                                                    $query->with(['accreditationContent' => function ($query) use ($category){
                                                        $query->where('accreditation_proposal_id', $category);
                                                    }])
                                                    ->with(['evaluationContent' => function ($query) use ($category) {
                                                        $query->join('evaluations', 'evaluations.id', '=', 'evaluation_contents.evaluation_id')
                                                        ->where('evaluations.accreditation_proposal_id', '=', $category)->first();
                                                    }])
                                                    ->get();
                                                }
                                            ]); // Load aspect points for each aspect
                                        },
                                    ]);
                                },       // Recursively load more children if needed
                
                                    'instrumentAspect' => function ($query) { // Load aspects of each component
                                    $query->with('instrumentAspectPoint'); // Load aspect points for each aspect
                                },
                                ]);
                            },
                            'instrumentAspect' => function ($query) { // Load aspects of main components
                                $query->with('instrumentAspectPoint'); // Load aspect points
                            },
                        ]);
                },
            ])
            ->first();




        return ($instrument);
    }
}
