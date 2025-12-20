<?php

namespace App\Http\Controllers\Amend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
Use Crypt;

class AmendDashboard extends Controller
{
    public function amenddashboard(){
        return view('amend.amenddashboard');
    }

    public function amendapplication(Request $request){

        try{
            $filterColumns = ['CREATED_AT','ID','CRF_NUMBER','CUSTOMER_ID','ACCOUNT_NO','CRF_STATUS','CREATED_BY','ACTIVE','ACTION','SOL_ID','VOLT_MATCH_FLAG'];

            $requestData = $request->get('data');
            $select_arr = [];
            $crfNumber = isset($requestData['crfNumber']) && $requestData['crfNumber'] != ''?$requestData['crfNumber']:'';
            $customerId = isset($requestData['customerId']) && $requestData['customerId'] != ''?$requestData['customerId']:'';

            $i = 0;
            $dt = array();
            foreach ($filterColumns as $column) {
                if($column == 'CRF_STATUS'){
                        array_push($select_arr, strtolower('CRF_STATUS'));
                        $dt[$i] = array('db'=> strtolower('CRF_STATUS'),'dt'=>$i,
                        'formatter'=>function($d,$row){
                            $html = '';
                            if($row->crf_status != ''){
                                $crfStatus = config('amend_status.CRF_STATUS');
                                $html = $crfStatus[$row->crf_status];
                            }
                            return $html;
                        });
                    }elseif($column == 'CREATED_BY'){
                        array_push($select_arr, strtolower('CREATED_BY'));
                        $dt[$i] = array('db'=>strtolower('CREATED_BY'),'dt'=>$i,
                            'formatter'=>function($d,$row){
                                $userData =  DB::table('USERS')->select(DB::raw('emp_first_name || emp_middle_name || emp_last_name AS empname'))->where('ID',$row->created_by)->get()->toArray();
                                $userName = (array)current($userData);

                                $html = isset($userName['empname']) && $userName['empname'] != ''?$userName['empname'] : '';
                                return $html;
                            });

                }elseif($column == 'ACTION'){
                    array_push($select_arr, DB::raw('CRF_NUMBER as action'));
                    $dt[$i] = array('db'=> DB::raw('CRF_NUMBER as action'),'dt' => $i,
                        'formatter' => function($d,$row){

                            $messageBtn = '';

                            if($row->volt_match_flag == ''){

                                switch ($row->crf_status) {
                                    case 22:
                                        $messageBtn = 'CRF-Review';
                                        break;
                                    case 23:
                                        $messageBtn = 'CRF-Upload';
                                        break;
                                    default:
                                        $messageBtn = 'View';
                                        break;
                                }

                            }else{

                                switch ($row->crf_status) {
                                    case 22:
                                        $messageBtn = 'CRF-KYCReview';
                                        break;
                                    default:
                                        $messageBtn = 'View';
                                        break;
                                }

                            }

                            return  $html = '<a href="javascript:invokePendingCRF('.$row->crf_number.')" id="'.$row->crf_number.'" class="amendPendingCrf">'.$messageBtn.'</a>';
                        });
                }else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                $i++;
            }
            //$dt_obj = (new SSP('AMEND_MASTER',$dt)); Code commented during laravel version upgrade
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('AMEND_MASTER')->select($select_arr);
            $dt_obj =  $dt_obj->where('CRF_NEXT_ROLE',2);
            $dt_obj =  $dt_obj->where('CRF_NUMBER','LIKE','%'.$crfNumber.'%');
            $dt_obj =  $dt_obj->where('CUSTOMER_ID','LIKE','%'.$customerId.'%');
            $dt_obj = $dt_obj->where('SOL_ID',Session::get('branchId'));

            $dt_obj = $dt_obj->orderBy('ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);

            // return response()->json($dt_obj->getDtArr());

            }catch(\Illuminate\Exception\QueryException $e){
                if(env('APP_CUBE_DEBUG')){dd($e->getMessage());}
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

}

