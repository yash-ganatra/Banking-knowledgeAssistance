<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Session;
use Cookie;
use Crypt;
use DB;


class EmailSmsMessagesLogController extends Controller
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
            $claims = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($claims),true);
            //get auditeeId by userDetails
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/EmailSmsMessagesLogController','emailsmsmessages','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    /*
     * Method Name: dashboard
     * Created By : Sharanya T
     * Created At : 12-02-2020
   
     * Description:
     * This function for Email SMS Log
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
     public function emailsmsmessages(Request $request)
    {
        try {

            //returns tempalte
            //$users = CommonFunctions::getusers();
            $activityCodes = DB::table('MESSAGES')->select('activity_code','id')
                                        ->where('is_active',1)
                                      ->pluck('activity_code','id')
                                      ->toArray();

            $log_refresh_timers = config('constants.LOG_REFRESH_TIMER');


            return view('admin.emailsmsmessages')->with('activityCodes', $activityCodes)
                                                ->with('log_refresh_timers', $log_refresh_timers);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getemailsmsmessageslist(Request $request)
    {
        try {
            $maxLength = 50;
            $array_column = [];
            $requestData = $request->get('data');
            $filteredColumns = ['EMAIL_SMS_MESSAGES.ID','EMAIL_SMS_MESSAGES.AOF_NUMBER','EMAIL_SMS_MESSAGES.ACTIVITY_CODE','EMAIL_SMS_MESSAGES.MESSAGE_TYPE','EMAIL_SMS_MESSAGES.EMAIL_ID','EMAIL_SMS_MESSAGES.ATTACHMENT','EMAIL_SMS_MESSAGES.MOBILE','EMAIL_SMS_MESSAGES.SENT_DATE','EMAIL_SMS_MESSAGES.IS_SENT','EMAIL_SMS_MESSAGES.CREATED_AT','EMAIL_SMS_MESSAGES.SMS_RESPONSE_CODE','EMAIL_SMS_MESSAGES.EMAIL_RESPONSE'];
            
            $i=0;
            foreach ($filteredColumns as $column) {
                array_push($array_column,'ID');
                if($column == "ID"){
                        $dt[$i] = array( 'db' => 'ID','dt' => $i,
                            'formatter' => function( $d, $row ) {

                                $html = ($row->id);
                                return $html;
                            }
                        );
                }else if($column == "AOF_NUMBER"){
                        array_push($array_column,'AOF_NUMBER');
                        $dt[$i] = array( 'db' => 'AOF_NUMBER','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = $row->aof_number;

                                return $html;
                            }
                        );
                }else if($column == "ACTIVITY_CODE"){
                        array_push($array_column,'ACTIVITY_CODE');
                        $dt[$i] = array( 'db' => 'ACTIVITY_CODE','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->activity_code);
                                return $html;
                            }
                        );
                }else if($column == "MESSAGE_TYPE"){
                        array_push($array_column,'MESSAGE_TYPE');
                        $dt[$i] = array( 'db' => 'MESSAGE_TYPE','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->message_type);
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_ID"){
                        array_push($array_column,'EMAIL_ID');
                        $dt[$i] = array( 'db' => 'EMAIL_ID','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->email_id);
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_SMS_MESSAGES.ATTACHMENT"){
                        array_push($array_column, strtolower('EMAIL_SMS_MESSAGES.ATTACHMENT'));
                        $dt[$i] = array( 'db' => strtolower('EMAIL_SMS_MESSAGES.ATTACHMENT'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $attachment = ($row->attachment);
                                if ($attachment == '') {
                                    $html = 'NO';
                                }else{
                                    $html = 'YES';
                                }
                                return $html;
                            }
                        );
                }else if($column == "MOBILE"){
                        array_push($array_column,'MOBILE');
                        $dt[$i] = array( 'db' => 'MOBILE','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->mobile);
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_SMS_MESSAGES.SENT_DATE"){
                        array_push($array_column, strtolower('EMAIL_SMS_MESSAGES.SENT_DATE'));
                        $dt[$i] = array( 'db' => strtolower('EMAIL_SMS_MESSAGES.SENT_DATE'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = '';
                                if ($row->sent_date != '') {
                                    $html = Carbon::parse($row->sent_date)->format('M d,g:i A');
                                }
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_SMS_MESSAGES.IS_SENT"){
                        array_push($array_column, strtolower('EMAIL_SMS_MESSAGES.IS_SENT'));
                        $dt[$i] = array( 'db' => strtolower('EMAIL_SMS_MESSAGES.IS_SENT'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $is_sent = ($row->is_sent);
                                if ($is_sent == 0) {
                                    $html = 'NO';
                                }else{
                                    $html = 'YES';
                                }
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_SMS_MESSAGES.CREATED_AT"){
                        array_push($array_column, strtolower('EMAIL_SMS_MESSAGES.CREATED_AT'));
                        $dt[$i] = array( 'db' => strtolower('EMAIL_SMS_MESSAGES.CREATED_AT'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                //$html = ($row->created_at);
                                $html = Carbon::parse($row->created_at)->format('M d,g:i A');

                                return $html;
                            }
                        );
                }else if($column == "SMS_RESPONSE_CODE"){
                        array_push($array_column,'SMS_RESPONSE_CODE');
                        $dt[$i] = array( 'db' => 'SMS_RESPONSE_CODE','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->sms_response_code);
                                return $html;
                            }
                        );
                }else if($column == "EMAIL_RESPONSE"){
                        array_push($array_column,'EMAIL_RESPONSE');
                        $dt[$i] = array( 'db' => 'EMAIL_RESPONSE','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = ($row->email_response);
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
            // $dt_obj = (new SSP('EMAIL_SMS_MESSAGES', $dt));
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('EMAIL_SMS_MESSAGES')->select($array_column);

            $dt_obj = $dt_obj->where("EMAIL_SMS_MESSAGES.CREATED_AT",'>=',Carbon::now()->subDays(1));
            
            if($requestData['activityCode'] != '')
            {
                $dt_obj = $dt_obj->where('activity_code',$requestData['activityCode']);
            }
           
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();

            $dd["items"] = (array) $dd["items"];
            $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
        
            return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
            //checks user name is empty or not
            //echo "<pre>";print_r($requestData);exit;

            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }        
    }

    public function getnotificationpending(Request $request){
        try{

            $sqlQuery = DB::table('EMAIL_SMS_MESSAGES')->select(DB::raw("to_char(CREATED_AT,'YYYY-MM-DD') as emaildate"),DB::raw("count(*) as forms"))
                                                        ->where('CREATED_AT','>=',Carbon::now()->subMonth(1))
                                                        ->where('IS_SENT',0)
                                                        ->orderBy(DB::raw("to_char(CREATED_AT,'YYYY-MM-DD')"),'DESC')
                                                        ->groupBy(DB::raw("to_char(CREATED_AT,'YYYY-MM-DD')"))
                                                        ->get()->toArray();

            return view('admin.notificationpending')->with('sqlQuery',$sqlQuery);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
}
?>
