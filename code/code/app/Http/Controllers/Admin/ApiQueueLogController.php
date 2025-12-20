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

class ApiQueueLogController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/apiservicelog','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

     public function apiqueuelog(Request $request)
    {
        try {
            return view('admin.apiqueuelog');
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function apiqueuelogtable(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData =  $request->get('data');
                $array_column = [];
                 $filteredColumns = ['API_QUEUE.ID','API_QUEUE.FORM_ID','ACCOUNT_DETAILS.AOF_NUMBER','API','API_QUEUE.APPLICANT_NO','STATUS',
                                                                                       'CREATED_BY','API_QUEUE.RETRY','API_QUEUE.NEXT_RUN'];
                $i=0;
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
                    }elseif ($column == "API") {
                           array_push($array_column,strtolower("API_QUEUE.API"));
                    $dt[$i] = array( 'db' => strtolower('API_QUEUE.API'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                        $html = '';
                                $config = config('constants.API_QUEUE');
                                $html = $config[$row->api];
                                return $html;
                            }
                        );
                    }
                    elseif ($column == "STATUS") {
                          array_push($array_column,strtolower("API_QUEUE.STATUS"));
                    $dt[$i] = array( 'db' => strtolower('API_QUEUE.STATUS'),'dt' => $i,
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

                // $dt_obj = (new SSP('API_QUEUE', $dt)); //old
                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);

                $dt_obj = DB::table('API_QUEUE')->select($array_column);

                $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','API_QUEUE.CREATED_BY');
                $dt_obj = $dt_obj->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','API_QUEUE.FORM_ID');
                $dt_obj = $dt_obj->where("API_QUEUE.CREATED_AT",'>=',Carbon::now()->subDays(10));


                if(isset($requestData['aofNumber']) && $requestData['aofNumber'] != ''){
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER', 'like', '%'.$requestData['aofNumber'].'%');
                    // $dt_obj = $dt_obj->where('API_QUEUE.STATUS', NULL);
                }

                $dt_obj = $dt_obj->orderBy('ID', 'DESC');

                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();
                $dd["items"] = (array) $dd["items"];
                $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
           
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

            //  return response()->json($dt_obj->getDtArr());
            }
         }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function updateapiqueue(Request $request)
    {
        try{
            if($request->ajax()){
                $requestData =  $request->get('data');
                if(isset($requestData['api_queue_id']) && $requestData['api_queue_id'] != ''){
                    $apiQueueData = DB::table('API_QUEUE')->select('PARAMETER','CLASS','API','RETRY','STATUS')->whereId($requestData['api_queue_id'])->get()->toArray();
                    if(count($apiQueueData) == 0){
                        return ['status' => 'fail','message' => 'No Record Found.Please Try Again'];
                    }
                    $apiQueueData = (array) current($apiQueueData);
                    if($apiQueueData['status'] == 'Y'){
                        return ['status' => 'success','message' => 'Record Already Updated'];
                    }
                    $retryCount = $apiQueueData['retry'] + 1;

                    $parameter = json_decode($apiQueueData['parameter']);
                    $className = app("App\\Helpers\\".$apiQueueData['class']);
                    $callFunction = call_user_func(array($className,$apiQueueData['api']), array($parameter));
                    if($callFunction['status'] == 'success'){
                        DB::table('API_QUEUE')->whereId($requestData['api_queue_id'])->update(['STATUS' => 'Y','UPDATED_AT' => Carbon::now(),'UPDATED_BY' => Session::get('userId'),'API_RESPONSE' => json_encode($callFunction)]);
                        return ['status' => 'success','message' => $callFunction['message']];
                    }else{
                        DB::table('API_QUEUE')->whereId($requestData['api_queue_id'])->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $retryCount,'UPDATED_BY' => Session::get('userId')]);
                        return ['status' => 'fail','message' => $callFunction['message']];
                    }
                }else{
                    return ['status' => 'fail','message' => 'Error! Please Try Again Later'];
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
