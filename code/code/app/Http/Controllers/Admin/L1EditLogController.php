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

class L1EditLogController extends Controller
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

    public function l1editlogs(Request $request)
    {
        try {
            return view('admin.l1editlogs');
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

     public function l1editlogstable(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData =  $request->get('data');
                //table coulmns to display
                $array_column = [];
                $filteredColumns = ['L1_EDIT_LOG.ID','L1_EDIT_LOG.FORM_ID','ACCOUNT_DETAILS.AOF_NUMBER','L1_EDIT_LOG.OLD_VALUE','L1_EDIT_LOG.NEW_VALUE','L1_EDIT_LOG.FIELD_NAME','L1_EDIT_LOG.APPLICANT_SEQUENCE','CREATED_BY','L1_EDIT_LOG.CREATED_AT'];
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
                    }else{
                        array_push($array_column,$column);
                        $dt[$i]['label'] = $column;
                        $dt[$i]['db'] = strtolower($column);
                        $dt[$i]['dt'] = $i;
                    }
                    $i++;
                }
                //  $dt_obj = (new SSP('L1_EDIT_LOG', $dt));
                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);

                $dt_obj = DB::table('L1_EDIT_LOG')->select($array_column);
                $dt_obj = $dt_obj->leftjoin('USERS','USERS.ID','L1_EDIT_LOG.CREATED_BY');
                $dt_obj = $dt_obj->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','L1_EDIT_LOG.FORM_ID');

                if(isset($requestData['formId']) && $requestData['formId'] != ''){
                    $dt_obj = $dt_obj->where('L1_EDIT_LOG.FORM_ID', 'like', '%'.$requestData['formId'].'%');
                }
                if(isset($requestData['aofNumber']) && $requestData['aofNumber'] != ''){
                    $dt_obj = $dt_obj->where('ACCOUNT_DETAILS.AOF_NUMBER', 'like', '%'.$requestData['aofNumber'].'%');
                }
                $dt_obj = $dt_obj->orderBy('ID', 'DESC');
                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();

                // $dd['items'] =  $dd['items'];
                $dd['items'] = array_map(fn($items) => array_values($items),$dd['items']);
               
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
                // return response()->json($dt_obj->getDtArr());
            }
         }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
}