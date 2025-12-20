<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use DB;
use Cookie;
use Crypt;
use Illuminate\Support\Arr;
use SoulDoit\DataTable\SSP;
use App\Helpers\CommonFunctions;
use Carbon\Carbon;

class ApiServiceLogController extends Controller
{  
	 /**
     * Create a new controller instance.
     *
     * @return void
     */
    //declare userId as global variable
    protected $userId;
    //declare roleId as global variable
    protected $roleId;

    public function __construct()
    {
        //checks token exists or not
        if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $claims = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($claims),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];
            
            if($this->roleId != 1){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/apiservicelog','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }


    /*
    * Method Name: datactivitylog
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to display users activities log
    *
    * Input Params:
    * @$users
    *
    * Output:
    * Returns view template
    */
    public function apiservicelog(Request $request)
    {
        try {
            //fetch all users
            $users = CommonFunctions::getusers();
            $serviceNames = config('constants.SERVICE_NAMES');
            $log_refresh_timers = config('constants.LOG_REFRESH_TIMER');
			
            //render mapping template
            return view('admin.apiservicelog')->with('modules',config('constants.MODULES'))
                                                ->with('serviceNames',$serviceNames)
                                                ->with('users',$users)
                                                ->with('log_refresh_timers',$log_refresh_timers)
                                                ;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



      /*
    * Method Name: sasactivitylogs
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to get users activities log
    *
    * Input Params:
    * @$user,@$module,@$startDate,@$endDate
    *
    * Output:
    * Returns Json.
    */
    public function apirequestlogs(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData =  $request->get('data');
                $maxId = DB::table('ENCRYPTED_API_SERVICE_LOG')->max('id');
                //table coulmns to display
                $array_column = [];
                $filteredColumns = ['ENCRYPTED_API_SERVICE_LOG.ID','ENCRYPTED_API_SERVICE_LOG.FORM_ID','ENCRYPTED_API_SERVICE_LOG.SERVICE_NAME','ENCRYPTED_API_SERVICE_LOG.SERVICE_URL','ENCRYPTED_API_SERVICE_LOG.SERVICE_REQUEST','ENCRYPTED_API_SERVICE_LOG.SERVICE_RESPONSE',
                                                                                        'CREATED_BY','ENCRYPTED_API_SERVICE_LOG.CREATED_AT'];
                $i=0;
                $maxLength = 50;
                //build dt array based on columns
                foreach ($filteredColumns as $column) {
                    if($column == "CREATED_BY"){
                        $user_name = "USERS.EMP_FIRST_NAME || ' ' || USERS.EMP_MIDDLE_NAME || ' ' || USERS.EMP_LAST_NAME AS user_name";
                        array_push($array_column,DB::raw($user_name));
                        $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = $row->user_name;
                                return $html;
                            }
                        );
                    }else if($column == "ENCRYPTED_API_SERVICE_LOG.CREATED_AT"){
                        array_push($array_column,strtolower('ENCRYPTED_API_SERVICE_LOG.CREATED_AT'));
                        $dt[$i] = array( 'db' => strtolower('ENCRYPTED_API_SERVICE_LOG.CREATED_AT'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = Carbon::parse($row->created_at)->format('M d,g:i A');
                                return $html;
                            }
                        );
                    }else if($column == "ENCRYPTED_API_SERVICE_LOG.SERVICE_REQUEST"){
                        array_push($array_column,strtolower('SERVICE_REQUEST'));
                        $dt[$i] = array( 'db' => strtolower('SERVICE_REQUEST'),'dt' => $i,
                            'formatter' => function( $d, $row ) use($maxLength) {
                                $html = '';
                              
                                if(strlen($row->service_request) > $maxLength){
                                    // $service_request = json_encode(htmlentities($row->service_request));
                                    $service_request = htmlentities($row->service_request);
                                    $html .= "<span class='mytooltip'>";
                                        $html .= "<span type='button' class='tooltipp service_modal' data-title='<pre>$service_request</pre>' data-type='Service Request' data-bs-toggle='modal' data-bs-target='#service_modal'>";
                                            $html .= trim(substr($row->service_request,0,$maxLength))."...";
                                        $html .= "</span>";
                                    $html .= "</span>";
                                }else{
                                    $html .= $row->service_request;
                                }
                                return $html;
                            }
                        );
                    }elseif($column == "ENCRYPTED_API_SERVICE_LOG.SERVICE_RESPONSE"){
                        array_push($array_column,strtolower('SERVICE_RESPONSE'));
                        $dt[$i] = array( 'db' => strtolower('SERVICE_RESPONSE'),'dt' => $i,
                            'formatter' => function( $d, $row ) use($maxLength) {
                                $html = '';
                                if(strlen($row->service_response) > $maxLength){
                                    $service_response = htmlentities($row->service_response);
                                    $html .= "<span class='mytooltip'>";
                                        $html .= "<span type='button' class='tooltipp service_modal' data-title='<pre>$service_response</pre>' data-type='Service Response' data-bs-toggle='modal' data-bs-target='#service_modal'>";
                                            $html .= trim(substr($row->service_response,0,$maxLength))."...";
                                        $html .= "</span>";
                                    $html .= "</span>";
                                }else{
                                    $html .= $row->service_response;
                                }
                                return $html;
                            }
                        );
                    }else{
                        array_push($array_column,$column);
                        $dt[$i]['label'] = $column;
                        $dt[$i]['db'] = strtolower($column);
                        $dt[$i]['dt'] = $i;
                    }             
                        $i++;
                }
                
                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);

                $dt_obj = DB::table('ENCRYPTED_API_SERVICE_LOG')->select($array_column);

                $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','ENCRYPTED_API_SERVICE_LOG.CREATED_BY');
                // $dt_obj = $dt_obj->where("ENCRYPTED_API_SERVICE_LOG.CREATED_AT",'>=',Carbon::now()->subDays(1));
                $dt_obj = $dt_obj->where("ENCRYPTED_API_SERVICE_LOG.ID",'>=',$maxId-2000);



                //checks start date is empty or not
                if(isset($requestData['startDate'])){
                    $dt_obj = $dt_obj->whereRaw("to_char(SCHEDULE_DATA_COPY_LOG.RUN_DATE,'DD-MM-YYYY')>='".$requestData['startDate']."'")
                                    ->whereRaw("to_char(SCHEDULE_DATA_COPY_LOG.RUN_DATE,'DD-MM-YYYY')<='".$requestData['endDate']."'");
                }

                if($requestData['userName'] != ''){
                    $dt_obj = $dt_obj->where("USERS.ID",$requestData['userName']);
                }

                if(isset($requestData['serviceName'])){
                    $dt_obj = $dt_obj->where("ENCRYPTED_API_SERVICE_LOG.SERVICE_NAME",$requestData['serviceName']);
                }

                if($requestData['formId'] != ''){

                    $dt_obj = $dt_obj->where("ENCRYPTED_API_SERVICE_LOG.FORM_ID",$requestData['formId']);
                }

                $aofNumberFormId = DB::table('ACCOUNT_DETAILS')->select('ID')
                                                               ->where('AOF_NUMBER',$requestData['aofNumber'])
                                                               ->get()->toArray();
                $aofNumberFormId = (array) current($aofNumberFormId);
                // echo "<pre>";print_r($aofNumberFormId);exit;
                if($requestData['aofNumber'] != '' && isset($aofNumberFormId['id'])){

                    $dt_obj = $dt_obj->where("ENCRYPTED_API_SERVICE_LOG.FORM_ID",$aofNumberFormId['id']);
                }

                // return response()->json($dt_obj->getDtArr());
                $dt_obj = $dt_obj->orderBy('ID', 'DESC');
                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();

                $dd["items"] = (array) $dd["items"];
                $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
           
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }












    
}
