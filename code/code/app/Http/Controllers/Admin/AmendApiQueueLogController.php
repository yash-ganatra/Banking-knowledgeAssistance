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
use App\Helpers\ApiCommonFunction;
use Carbon\Carbon;
use Session;

class AmendApiQueueLogController extends Controller
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
            
            if(!in_array($this->roleId,[1,8])){
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/amendapiservicelog','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }
    public function amendapiqueuelog(){
        try{

            return view('admin.amendapiqueuelog');

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function amendapiqueuelogtable(Request $request){

        try{
            $requestData = $request->get('data');
            $array_column = [];
            $filteredColumns = ['AMEND_API_QUEUE.ID','AMEND_API_QUEUE.CRF_NUMBER','AMEND_API_QUEUE.MODULE','AMEND_API_QUEUE.SEQUENCE','STATUS','CREATED_BY','AMEND_API_QUEUE.RETRY','AMEND_API_QUEUE.NEXT_RUN'];
                $i=0;
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
                     }elseif($column == "MODULE"){
                        array_push($array_column,'MODULE');

                        $dt[$i] = array('db'=>'MODULE','dt'=>$i,
                        'formatter' => function($d,$row){
                            $html = '';
                            $html = $row->module;
                            return $html;
                        }
                      );
                    }elseif ($column == "STATUS") {
                       array_push($array_column,strtolower("AMEND_API_QUEUE.STATUS"));
                    $dt[$i] = array( 'db' => strtolower('AMEND_API_QUEUE.STATUS'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                            $html = '';
                                if($row->status == 'Y'){
                                    $html = 'Done';
                                }
                                return $html;
                            }
                        );
                    }

                    else{
                    array_push($array_column,$column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;
                }
                    $i++;
                }

                // $dt_obj = (new SSP('AMEND_API_QUEUE', $dt)); old

                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);

                $dt_obj = DB::table('AMEND_API_QUEUE')->select($array_column);
                $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','AMEND_API_QUEUE.CREATED_BY');
                $dt_obj = $dt_obj->where("AMEND_API_QUEUE.CREATED_AT",'>=',Carbon::now()->subDays(10));
                $dt_obj = $dt_obj->where('AMEND_API_QUEUE.CRF_NUMBER', 'like', '%'.$requestData['crfNumber'].'%');
                $dt_obj = $dt_obj->orderBy('AMEND_API_QUEUE.ID','ASC');
                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();

                $dd["items"] = (array) $dd["items"];
                $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
           
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
            //  return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateamendapiqueue(Request $request){
        try{
            if($request->ajax()){
                $requestData =  $request->get('data');
                    $id = isset($requestData['amednapi_queue_id']) && $requestData['amednapi_queue_id'] != ''?$requestData['amednapi_queue_id']:'';
                    $crfNumber = $requestData['crfNumber'];
                    if($id == ''){

                        if(env('APP_SETUP') == 'DEV'){
                            $schema = 'NPC';
                        }else{
                            $schema = 'CUBE';
                        }    
                    
                        $amendapiQueueData = DB::select("SELECT * FROM $schema.AMEND_API_QUEUE WHERE (CRF_NUMBER,SEQUENCE) IN (SELECT CRF_NUMBER,MIN(SEQUENCE) FROM $schema.AMEND_API_QUEUE  WHERE STATUS IS NULL AND CRF_NUMBER = $crfNumber GROUP BY CRF_NUMBER) ORDER BY CRF_NUMBER ASC");

                        $getData =  (array) current($amendapiQueueData);
                        $id = isset($getData['id']) && $getData['id'] !=''?$getData['id']:'';

                        if($id == ''){
                            return ['status' => 'fail','message' => 'No Record Found.Please Try Again'];
                        }
            
                    }else{

                        $amendapiQueueData = DB::table('AMEND_API_QUEUE')->select('PARAMETER','CLASS','API','RETRY','STATUS')
                                                                            ->whereNull('STATUS')
                                                                            ->whereId($id)
                                                                            ->get()->toArray();
                    }
                    if(count($amendapiQueueData) == 0){
                        return ['status' => 'fail','message' => 'No Record Found.Please Try Again'];
                    }
                    $amendapiQueueData = (array) current($amendapiQueueData);
                    if($amendapiQueueData['status'] == 'Y'){
                        return ['status' => 'success','message' => 'Record Already Updated'];
                    }
                    $retryCount = $amendapiQueueData['retry'] + 1;

                    $parameter = json_decode($amendapiQueueData['parameter']);
                    // echo "<pre>";print_r($parameter);exit;
                    $className = app("App\\Helpers\\".$amendapiQueueData['class']);
                    $callFunction = call_user_func_array(array($className,$amendapiQueueData['api']),$parameter);
                    $callFunction = json_decode($callFunction,true);

                    if(isset($callFunction['status']) &&  $callFunction['status'] == 'success'){
                        DB::table('AMEND_API_QUEUE')->whereId($id)->update(['STATUS' => 'Y','UPDATED_AT' => Carbon::now(),'UPDATED_BY' => Session::get('userId'),'API_RESPONSE' => json_encode($callFunction)]);
                        return ['status' => 'success','message' => $callFunction['msg']];
                    }else{
                        DB::table('AMEND_API_QUEUE')->whereId($id)->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $retryCount,'UPDATED_BY' => Session::get('userId')]);
                        return ['status' => 'fail','message' => $callFunction['msg']];
                    }

            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
}
