<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
Use Crypt;
use Cache;

class DiscrepancyReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if($this->roleId != 13){

                $isAutherized = false;
            }else{

               $isAutherized = true;
            }
            if(!$isAutherized)
            {
                echo "<div class='container RefreshRestrictMsg' style='
                    width: 63%;
                    margin: 0 auto;
                    height: 4em;
                    padding: 2em;
                    text-align: center;
                    border-radius: 10px red;
                    border-radius: 6px;
                    margin-top: 12em;
                    background-color: #fff0d3;
                    font-family:Arial;
                    line-height: 35px;'>

                    <p style='margin-top: 0%;
                    font-size: 1.375rem;
                    font-weight: 500;'>Unauthorized attempt detected.<br>Event logged for Audit and Admin team.</p>
                 
                  </div>";
                $saveuserlog = CommonFunctions::createUserLogDirect('Management/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function discrepancyreport(){
        try{
             
            // fetch user names from applcations
              $customerNames = CommonFunctions::getCustomerDetails(3);
              //returns tempalte
              return view('manco.discrepancyreport')->with('customerNames',$customerNames);
                 
          }
          catch(\Illuminate\Database\QueryException $e) {
              if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
              $eMessage = $e->getMessage();
              //CommonFunctions::addExceptionLog($eMessage, $request);
              CommonFunctions::addLogicExceptionLog('Management/DiscrepancyReportController','discrepancyreport',$eMessage);
              return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
          }
    }

    public function getdiscrepancyreport(Request $request){

        try {
            //fetch data from request
            $requestData = $request->get('data');
             $select_arr=[];
            $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
                                   
            $filteredColumns=['AOF_NUMBER','CREATION_DATE','USER_NAME','ACCOUNT_TYPE','CUSTOMER_ID','ACCOUNT_NO_OPENED','ACC_OPENING_DATE','FUNDING_VALUE','EKYC','HRMS','SCHEME_CODE','AOF_STATUS','DISCREPANCY_DETAILS'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {

                if($column == "USER_NAME")
                {
                    $user_name = "COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name";
                   array_push($select_arr,DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            
                            return $html;
                        }
                    );
                }
                else if($column == "CUSTOMER_ID"){

                   array_push($select_arr,"COD.CUSTOMER_ID");
                    $dt[$i] = array( 'db' => 'COD.customer_id','dt' => $i,
                    'formatter' => function( $d, $row ) {
                       $row = (array) $row;                                   
                        $html = $row['customer_id'];
                        
                        return $html;
                    }
                  );
                }

                else if($column == "ACCOUNT_NO_OPENED"){
                     array_push($select_arr,"ACCOUNT_DETAILS.ACCOUNT_NO");
                    $dt[$i] = array( 'db' => 'account_details.account_no','dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                           $html = $row['account_no'];                                        
                            return $html;
                        }
                    );
                }

              else if($column == "ACC_OPENING_DATE"){                    
                array_push($select_arr,"ACCOUNT_DETAILS.CREATED_AT AS acc_opening_date");
                $dt[$i] = array(
                    'db' => 'acc_opening_date',
                    'dt' => $i,
                    'formatter' => function($d, $row) {
                        $row = (array) $row;
                        $html = \Carbon\Carbon::parse($row['acc_opening_date'])->format('d-m-Y h:i:s');
                        return $html;
                    }
                );
            }


            else if($column == "CREATION_DATE"){                    
    
                array_push($select_arr,"ACCOUNT_DETAILS.CREATED_AT AS creation_date");
                $dt[$i] = array(
                    'db' => 'creation_date',
                    'dt' => $i,
                    'formatter' => function($d, $row) {
                        $row = (array) $row;
                        $html = \Carbon\Carbon::parse($row['creation_date'])->format('d-m-Y h:i:s');
                        return $html;
                    }
                );
            }
                else if($column == "FUNDING_VALUE"){
                    $amount = "COD.AMOUNT AS amount";
                      array_push($select_arr,$amount);
                       $dt[$i] = array( 'db' => DB::raw($amount),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                           $html = $row['amount'];                                        
                            return $html;
                        }
                    );
                }

                 else if($column == "EKYC"){
                   $id_proof = "COD.PROOF_OF_IDENTITY AS id_proof";
                    array_push($select_arr,$id_proof);
                    $dt[$i] = array( 'db' => DB::raw($id_proof),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            if($row->id_proof == 9){

                              $html = 'EKYC';
                            }else{

                              $html = 'NON-EKYC';
                            }


                            return $html;
                        }
                    );
                }

                else if($column == "CUSTOMER_TYPE"){
                   array_push($select_arr,"ACCOUNT_DETAILS.IS_NEW_CUSTOMER");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.IS_NEW_CUSTOMER'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) ($row);
                            if($row['flow_type'] == ''){

                            if ($row['delight_scheme'] != '' && $row['account_type'] == 'Savings') {
                                $html = 'DELIGHT';
                            }else if($row['is_new_customer'] == 1){
                                 $html = 'NTB';
                            }else{
                                $html = 'ETB';
                            }
                            }else{
                                $html = $row['flow_type'];
                            }
                                                    
                            return $html;
                        }
                    );
                }
                else if($column == "ACCOUNT_TYPE"){
                    array_push($select_arr,"ACCOUNT_TYPES.ACCOUNT_TYPE");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_TYPES.ACCOUNT_TYPE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                           $row = (array) $row;
                                                 
                            $html = $row['account_type'];
                            return $html;
                        }
                    );
                }else if($column == "HRMS"){
                    $hrms_no = "USERS.HRMSNO AS hrms_no";
                    array_push($select_arr,DB::raw($hrms_no));
                    $dt[$i] = array( 'db' => DB::raw($hrms_no),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = '';
                            if (isset($row->hrms_no)) {
                                $html = $row->hrms_no;
                            }
                            return $html;
                        }
                    );
                    }
                else if($column == "SCHEME_CODE"){

                   array_push($select_arr,"ACCOUNT_DETAILS.SCHEME_CODE");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.SCHEME_CODE'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            
                            $schemeCode = $row['scheme_code'];
                            $accountType = $row['account_type'];

                            if($row['account_type'] == 'Savings'){

                             $getSchemeDesc = CommonFunctions::getSchemeCodesDesc($accountType,$schemeCode);
                             $html = $getSchemeDesc->scheme_code;

                            }
                            elseif($row['account_type'] == 'Term Deposit'){

                                $getTDSchemeDesc = CommonFunctions::getTDSchemeCodesDesc($accountType,$schemeCode);
                                $html = $getTDSchemeDesc->scheme_code;
                            }
                            else{

                                $getComboSchemeDesc = CommonFunctions::getSchemeCodesDesc($accountType,$schemeCode);
                                $html = $getComboSchemeDesc->scheme_code;
                            }
    
                            return $html;
                        }
                    );
                } 
                
                else if($column == "DISCREPANCY_DETAILS"){                    
                   array_push($select_arr,"ACCOUNT_DETAILS.ID");
                    $dt[$i] = array( 'db' => strtolower('ACCOUNT_DETAILS.ID'),'dt' => $i,
                    'formatter' => function( $d, $row ) {
                        $row = (array) $row; 
                                                              
                        $formID = $row['id'];
                        $getDiscreptionDetails = CommonFunctions::getDiscreptionDetails($formID); 
                        $html=$getDiscreptionDetails;

                        return $html;

                    }
                );
                }elseif ($column == "AOF_STATUS") {
    
                    $formID = "ACCOUNT_DETAILS.ID AS account_id";
                    array_push($select_arr, DB::raw($formID)); 
                    $dt[$i] = array('db' => 'account_id','dt' => $i,'formatter' => function($d, $row) {
                            $getAofStatus = CommonFunctions::getAofStatus($row->account_id); // Access using alias
                            $html = isset($getAofStatus['status']) && $getAofStatus['status'] != '' ? $getAofStatus['status'] : '';
                            return $html;
                        }
                    );
                }

                else{
                 array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }                
                $i++;              
            }

            // $dt_obj = new SSP('ACCOUNT_DETAILS', $dt);
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);
            
            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);

            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY');            
            $dt_obj = $dt_obj->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE');            
            // $dt_obj = $dt_obj->leftjoin('AOF_STATUS','AOF_STATUS.ID','ACCOUNT_DETAILS.APPLICATION_STATUS');          
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(1));             
            //  $dt_obj = $dt_obj->leftjoin('STATUS_LOG AS STL','STL.FORM_ID','ACCOUNT_DETAILS.ID'); 
           
            if($requestData['aofnumber'] != '')
            {
                $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER','like','%'.$requestData['aofnumber'].'%');
            }        

            //checks sent date is empty or not
            if($requestData['startDate'] != '')
            {

                $date1=carbon::parse($requestData['startDate']);
                $date2=carbon::parse($requestData['endDate']);

                $diffrence=$date1->diffInDays($date2);

                $threeMonth= Carbon::now()->submonths(3);
                $count=$threeMonth->diffInDays(Carbon::now());

                if($diffrence >$count){
                    return json_encode(['status'=>'fail','msg'=>'Please select a date range within 3 months','data'=>[]]);
                }
                $dt_obj = $dt_obj
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT >= to_date('".$requestData['startDate']."','DD-MM-YYYY')")
                ->whereRaw("ACCOUNT_DETAILS.CREATED_AT <= to_date('".$requestData['endDate']."','DD-MM-YYYY')");
            }else{
                
                if($requestData['aofnumber'] == ''){
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.CREATED_AT','>=',Carbon::now()->subMonths(1));   
                }
            }          
           
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.IS_ACTIVE',1);           
            $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.NEXT_ROLE','!=',9);        
            $dt_obj = $dt_obj->where('COD.APPLICANT_SEQUENCE',1);
          
              $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->orderBy("ACCOUNT_DETAILS.ID","DESC");
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);

            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

            
            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);        
        }
   

    }

}
