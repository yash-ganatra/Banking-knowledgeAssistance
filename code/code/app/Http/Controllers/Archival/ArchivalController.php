<?php

namespace App\Http\Controllers\Archival;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Crypt,Cache,Session;
use Carbon\Carbon;
use Cookie;
use DB;

class ArchivalController extends Controller
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

            if($this->roleId != 9){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Archival/ArchivalController','ArchivalController','Unauthorized attempt detected by '.$this->userId,'','','1');

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



    public function updatearchivalno(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = $request->get('data');
                DB::beginTransaction();
                $updatearchivalno = DB::table('ARCHIVAL')->whereId($requestData['id'])
                                                            ->Updated(['ARCHIVAL_NO'=>$requestData['archival_no']]);
                if($updatearchivalno){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Response Updated Successfully','data'=>[]]);
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addarchivalno(Request $request)
    {
        try{
            
            //returns tempalte
            return view('archival.addarchivalno');
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function archivallist(Request $request)
    {
        try{
            //fetch data from request
            $requestData = $request->get('data');
            $select_arr = [];
            //build columns array           
            $filteredColumns = ['ACCOUNT_DETAILS.AOF_NUMBER','ACCOUNT_DETAILS.ACCOUNT_NO','ACCOUNT_DETAILS.CUSTOMER_ID','USER_NAME','ARCHIVAL.ARCHIVAL_REF_ONE','ARCHIVAL.ARCHIVAL_REF_TWO','ACCOUNT_DETAILS.CREATED_AT','ACTION','ACCOUNT_DETAILS.ID'];
            $i=0;
            //build dt array
            foreach ($filteredColumns as $column) {

                if($column == "USER_NAME")
                {
                    $user_name = "COD.FIRST_NAME || ' ' || COD.MIDDLE_NAME || ' ' || COD.LAST_NAME AS user_name";
                    array_push($select_arr, DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            return $html;
                        }
                    );
                }
                else if($column == "CUSTOMER_ID"){
                    array_push($select_arr, 'CUSTOMER_ID');
                    $dt[$i] = array( 'db' => 'CUSTOMER_ID','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            // $html = ucfirst(config('constants.ACCOUNT_TYPES.'.$row->account_type));                     
                            $html = $row->customer_id;                     
                            return $html;
                        }
                    );
                }
                else if($column == "CREATED_AT"){
                    array_push($select_arr, 'CREATED_AT');
                    $dt[$i] = array( 'db' => 'CREATED_AT','dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = \Carbon\Carbon::parse($row->created_at)->format('d M Y');
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr, strtolower('ARCHIVAL.ID'));
                    $dt[$i] = array( 'db' => strtolower('ARCHIVAL.ID'),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $row = (array) $row;
                            $html = '<a href="javascript:void(0)" id="'.$row['id'].'" data-archivalId="'.$row['id'].'"
                                            data-toggle="modal" data-target="#addarchivalno" class="addarchivalno">Edit</a>';                     
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
            //$dt_obj = new SSP('ACCOUNT_DETAILS', $dt); Commented line during version upgrade
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('ACCOUNT_DETAILS')->select($select_arr);
            $dt_obj = $dt_obj->leftjoin('ARCHIVAL','ARCHIVAL.ID','ACCOUNT_DETAILS.ARCHIVAL_ID');
            $dt_obj = $dt_obj->leftjoin('CUSTOMER_OVD_DETAILS AS COD','COD.FORM_ID','ACCOUNT_DETAILS.ID');
            $dt_obj = $dt_obj->where('APPLICANT_SEQUENCE',1)->where('ACCOUNT_DETAILS.APPLICATION_STATUS','=',20);
            
            $dt_obj = $dt_obj->orderBy('ACCOUNT_DETAILS.ID', 'DESC');

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
            //return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function savearchival(Request $request)
    {
        try{
            if ($request->ajax()){
                //fetch data from request
                $requestData = Arr::except($request->get('data'),['functionName','id']);
                $formId = $request->get('data')['id'];

                // Begins db transaction
                DB::beginTransaction();
                if(isset($requestData['archivalId']))
                {
                    $archivalId = $requestData['archivalId'];
                    $requestData = Arr::except($requestData,'archivalId');
                    $archivalNo = DB::table('ARCHIVAL')->whereId($archivalId)->update($requestData);
                }else{
                    $archivalNo = DB::table('ARCHIVAL')->insertGetId($requestData);
                    $updateAachivalNo = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                                            ->update(['ARCHIVAL_ID'=>$archivalNo,
                                                                                        'APPLICATION_STATUS'=>21]);
                }

                if($archivalNo){
                    //commit database if response is true
                    DB::commit();                   
                    return json_encode(['status'=>'success','msg'=>'Archival Number Updated Successfully','data'=>[]]);
                }else{
                    //rollback db transactions if any error occurs in query
                    DB::rollback();
                    return json_encode(['status'=>'fail','msg'=>'No records found.','data'=>[]]);
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
?>