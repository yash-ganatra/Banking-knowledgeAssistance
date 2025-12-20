<?php

namespace App\Http\Controllers\Uam;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Illuminate\Support\Arr;
use Session;
use Cookie;
use Crypt;
use DB;
use Carbon\Carbon;

class UamDashboardController extends Controller
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

            if(!in_array($this->roleId,[1,12])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('UAM/UamDashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }
    

    /*
     * Method Name: dashboard
     * Created By : Sharanya T
     * Created At : 12-02-2020
   
     * Description:
     * This function is fetch savings , current and fixed deposite accounts Count and customer names
     *
     * Input Params:
     * @params
     *
     * Output:
     * Returns template.
    */
    public function dashboard()
    {
        //returns tempalte
        $users = CommonFunctions::getuamusers();
        //echo "<pre>";print_r($users);exit;
        return view('uam.dashboard')->with('users',$users);
    }

     public function getuamuserslist(Request $request)
    {
        try {
            $maxLength = 0;
            if($request['length'] == -1)
            {
                $maxLength = $request['length'];
            }

            $requestData = $request->get('data');
            $select_arr=[];
            $filteredColumns = ['USERS.ID','USERS.HRMSNO','USERS.EMPSOL','EMP_NAME','USERS.EMPLDAPUSERID',
                                'USERS.EMPBUSINESSUNIT','USERS.EMPLOCATION','USERS.EMPBRANCH','USER_ROLES.ROLE','ACTIVE','ACTION','USERS.EMPSTATUS'];
            $i=0;
            foreach ($filteredColumns as $column) {
                if($column == "EMP_NAME")
                {
                    $name_columns = "EMP_FIRST_NAME || ' ' || EMP_MIDDLE_NAME || ' ' || EMP_LAST_NAME AS emp_name";
                    array_push($select_arr,DB::raw($name_columns));
                    $dt[$i] = array( 'db' => DB::raw($name_columns),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->emp_name;
                            return $html;
                        }
                    );
                }else if($column == "ACTION"){
                    array_push($select_arr,"USERS.ID AS action");
                    $dt[$i] = array( 'db' => 'action','dt' => $i,
                        'formatter' => function( $d, $row )
                        {
                            if (isset($row->hrmsno)) {
                                $html = '<a href="javascript:void(0)" id="HR_'.$row->hrmsno.'" class="edit_user">Edit</a>'; 
                            }else{
                                $html = '<a href="javascript:void(0)" id="ID_'.$row->id.'" class="edit_user">Edit</a>'; 
                            }
                            return $html;                           
                        }
                    );
                }else if($column == "ACTIVE"){
                   array_push($select_arr,"USERS.ID AS active");
                    $dt[$i] = array( 'db' => 'active','dt' => $i,
                        'formatter' => function( $d, $row ) use($maxLength) {
                            if($maxLength == -1){
                                if($row->empstatus == "Y"){
                                    $html = 'Yes';
                                }else{
                                    $html = 'No';
                                }
                            }else{
                                $html = '';
                                $html .= '<div class="tabledit-toolbar btn-toolbar" style="text-align: left;" id="changeStatus">';
                                    $html .= '<div class="btn-group btn-group-sm" style="float: none;">'; 
                                    if($row->empstatus == "Y"){
                                        $html .= '<button class="user_status_active" id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#uamdashModal"></button>';
                                    }else{
                                       $html .= '<button class="user_status_inactive" id="'.$row->id.'"  data-bs-toggle="modal" data-bs-target="#uamdashModal"></button>';
                                    }
                                    $html .= '</div>';
                                $html .= '</div>';
                            }
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
            // $dt_obj = (new SSP('USERS', $dt));
             $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt);

            $dt_obj = DB::table('USERS')->select($select_arr);
            $dt_obj = $dt_obj->leftJoin('USER_ROLES','USER_ROLES.ID','USERS.ROLE');
            $dt_obj = $dt_obj->where('EMPSTATUS','<>','D');


            //checks user name is empty or not
            if($requestData['users'] != '')
            {
                $dt_obj = $dt_obj->where('USERS.ID',$requestData['users']);
            }

            $dt_obj = $dt_obj->orderBy('ID', 'DESC');
            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            $dd['items'] = (array) $dd['items'];
            $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

            return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
            // return response()->json($dt_obj->getDtArr());
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }        
    }

    public function uamupdateuserstatus(Request $request)
    {
        try {
            if ($request->ajax()) {
                DB::beginTransaction();
        
                $curr_date = Carbon::now();
            $requestData = Arr::except($request->get('data'), 'functionName');
            $userid = Session::get('userId');

                $users = DB::table('USERS')->whereId($requestData['id'])->update(['EMPSTATUS'=>$requestData['status'],
                                                                                  'UPDATED_AT'=>$curr_date]);

            if (($requestData['status'] == 'Y') && (isset($requestData['type']) && $requestData['type'] == 'Y')) {
                $employee = DB::table('USERS as usr')->select(
                    'usr.ID', 'usr.hrmsno', 'usr.empsol', 'usr.emp_first_name',
                    'usr.emp_middle_name', 'usr.emp_last_name', 'usr.empldapuserid',
                    'usr.empbusinessunit', 'usr.emplocation', 'usr.empbranch',
                    'ur.role as role', 'usr.empstatus',
                    DB::raw("usr.emp_first_name || ' ' || usr.emp_middle_name || ' ' || usr.emp_last_name AS emp_name")
                )
                ->leftJoin('USER_ROLES as ur','ur.ID','usr.ROLE')
                ->where('usr.ID', $requestData['id']) 
                ->get()->toArray();

                $employee = (array) current($employee);
                    $dataToInsert = [
                        'USER_ID' => $employee['id'],
                        'HRMSNO' => $employee['hrmsno'],
                        'EMPSOL' => $employee['empsol'],
                        'EMP_FIRST_NAME' => $employee['emp_first_name'],
                        'EMP_MIDDLE_NAME' => $employee['emp_middle_name'],
                        'EMP_LAST_NAME' => $employee['emp_last_name'],
                        'EMPLDAPUSERID' => $employee['empldapuserid'],
                        'EMPBUSINESSUNIT' => $employee['empbusinessunit'],
                        'EMPLOCATION' => $employee['emplocation'],
                        'EMPBRANCH' => $employee['empbranch'],
                        'EMPSTATUS' => $employee['empstatus'],
                        'ROLE' => $employee['role'],
                        'CREATED_BY' => $userid,
                        'CREATED_AT' => now(),
                        'UPDATED_BY' => '',
                        'UPDATED_AT' => '',
                    ];
                    DB::table('USER_UNDELETE')->insert($dataToInsert);
            }

            // Commit or rollback transaction
            if ($users) {
                    DB::commit();
                return response()->json(['status' => 'success', 'msg' => 'User Status Updated Successfully.', 'id' => $requestData['id']]);
            } else {
                    DB::rollback();
                return response()->json(['status' => 'warning', 'msg' => 'Error! Please try again later.', 'id' => []]);
            }
        }
    } catch (\Illuminate\Database\QueryException $e) {
        DB::rollback();
        if (env('APP_CUBE_DEBUG')) {
            dd($e->getMessage());
        }
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
        return response()->json(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
}

    public function uamedituser(Request $request)
    {
        try {
            $employeeDetails = array();
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $data = base64_decode($decodedString);
            $data = explode('_', $data);
            $hrmsNo = $data[1];
            $typeOfEdit = $data[0];
            //echo "<pre>";print_r($data);exit;
            if ($typeOfEdit == 'ID') {
                $table = 'USERS';
                $userData = DB::table($table)->whereId($hrmsNo)->get()->toArray();
                if (count($userData) > 0) {
                    $userData = (array) current($userData);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Data Error! Please try again','data'=>[]]);
                }
                $branchlists = CommonFunctions::getBranch();
                $regionlists = CommonFunctions::getRegional();
                $zonelists = CommonFunctions::getZone();
                $clusterlists = CommonFunctions::getCluster();
                $rolelists = CommonFunctions::getRoles();

                return view('uam.addusertabledata')
                                                ->with('userData',$userData)
                                                ->with('branchlists',$branchlists)
                                                ->with('regionlists',$regionlists)
                                                ->with('zonelists',$zonelists)
                                                ->with('clusterlists',$clusterlists)
                                                ->with('rolelists',$rolelists)
                                                ->with('table',$table);
            }
            $employeeApiDetails = (array) Api::hrapiconnection($hrmsNo);
            $employeeDetails = DB::table('USERS')->where('HRMSNO',$hrmsNo)->get()->toArray();
            if(count($employeeDetails) > 0)
            {
                $employeeDetails = (array) current($employeeDetails);
            }
            $roles = DB::table('USER_ROLES')->pluck('role','id')->toArray();
            $role_types = config('constants.BRANCH_REVIEWER_TYPE');
            $zones = DB::table('ZONE_MASTER')->pluck('zone_name','id')->toArray();
            $clusters = DB::table('CLUSTER_MASTER')->pluck('cluster_name','id')->toArray();
            $regionals = DB::table('REGION_MASTER')->pluck('region_name','id')->toArray();
            //render dashboard template
            return view('uam.edituser')->with('roles',$roles)
                                        ->with('clusters',$clusters)
                                        ->with('regionals',$regionals)
                                        ->with('zones',$zones)
                                        ->with('role_types',$role_types)
                                        ->with('employeeDetails',$employeeDetails)
                                        ->with('employeeApiDetails',$employeeApiDetails);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function uamadduser(Request $request)
    {
        try {
            $roles = DB::table('USER_ROLES')->pluck('role','id')->toArray();
            $role_types = config('constants.BRANCH_REVIEWER_TYPE');
            $zones = DB::table('ZONE_MASTER')->pluck('zone_name','id')->toArray();
            $clusters = DB::table('CLUSTER_MASTER')->pluck('cluster_name','id')->toArray();
            $regionals = DB::table('REGION_MASTER')->pluck('region_name','id')->toArray();
            //render dashboard template
            return view('uam.adduser')->with('roles',$roles)
                                      ->with('clusters',$clusters)
                                      ->with('regionals',$regionals)
                                      ->with('zones',$zones)
                                      ->with('role_types',$role_types);

        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getuamuserdetailsbyid(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from ajax call
                $requestData = $request->get('data');
                $hrmsNo = $requestData['emp_id'];
                if(!is_numeric($hrmsNo)){
                    return json_encode(['status'=>'error','msg'=>'Not Found.','employeeDetails'=>[]]);
                }

                $employeeDetails = DB::table('USERS')->where('HRMSNO',$hrmsNo)->get()->toArray();
                
                if(count($employeeDetails)>0){
                    return json_encode(['status'=>'fail','msg'=>'User already exist!','employeeDetails'=>[]]);
                }
         
                $employeeApiDetails = Api::hrapiconnection($hrmsNo);
                $roles = DB::table('USER_ROLES')->pluck('role','id')->toArray();
                $role_types = config('constants.BRANCH_REVIEWER_TYPE');
                $zones = DB::table('ZONE_MASTER')->pluck('zone_name','id')->toArray();
                $clusters = DB::table('CLUSTER_MASTER')->pluck('cluster_name','id')->toArray();
                $regionals = DB::table('REGION_MASTER')->pluck('region_name','id')->toArray();
                if(count($employeeDetails) > 0)
                {
                    $employeeApiDetails = (array) $employeeApiDetails;
                    $employeeDetails = (array) current($employeeDetails);
                    return view('admin.userdetails')->with('roles',$roles)
                                                    ->with('clusters',$clusters)
                                                    ->with('regionals',$regionals)
                                                    ->with('zones',$zones)
                                                    ->with('role_types',$role_types)
                                                    ->with('employeeDetails',$employeeDetails)
                                                    ->with('employeeApiDetails',$employeeApiDetails)
                                                    ;
                }
                if($employeeApiDetails){
                    //returns employee details
                    return json_encode(['status'=>'success','msg'=>'Employee Details.','employeeDetails'=>$employeeApiDetails]);
                }else{
                    return json_encode(['status'=>'error','msg'=>'Not Found.','employeeDetails'=>[]]);
                }
                
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function uamsaveuserdeatils(Request $request)
    {
        try {
            if ($request->ajax()) {
                //fetch data from ajax call
                $requestData = Arr::except($request->get('data'),'functionName');
                
                if(isset($requestData['EMPLDAPUSERID']) && $requestData['EMPLDAPUSERID'] == ''){
                    return json_encode(['status'=>'fail','msg'=>'Data invalid Api response, Please try again later.','data'=>[]]);
                }

                if($requestData['ROLE'] == '2'){
                    $sourceCodeDetails = CommonFunctions::fetchSourceCodeDetails($requestData['HRMSNO']);
                    if (!$sourceCodeDetails) {
                        return json_encode(['status'=>'fail','msg'=>'Source code does not exist in Finacle','data'=>[]]);
                    }
                }

                if ($requestData['RM_CODE'] == '' && $requestData['ROLE'] == '2') {
                    return json_encode(['status'=>'fail','msg'=>'Please enter mandatory RM Code.','employeeDetails'=>$requestData]);    
                }

                if ($requestData['RM_CODE'] != '') {
                    $fetchRmCodeDetails = CommonFunctions::fetchRmCodeDetails($requestData['RM_CODE'], $requestData['HRMSNO']);
                    if (!$fetchRmCodeDetails) {
                        return json_encode(['status'=>'fail','msg'=>'RM code does not exist in Finacle','data'=>[]]);
                    }
                }

                $userName = $requestData['EMP_NAME'];
                $user = implode(' ', array_filter(explode(' ', $requestData['EMP_NAME'])));
                $user = explode(' ', $user);
                
                $requestData['EMP_FIRST_NAME'] = isset($user[0]) && $user[0] !=''?$user[0]:'';
                $requestData['EMP_MIDDLE_NAME'] = isset($user[1]) && $user[1] !=''?$user[1]:'';
                $requestData['EMP_LAST_NAME'] = isset($user[2]) && $user[2] !=''?$user[2]:'';
                $requestData['EMPSTATUS']  = 'Y';

                if($requestData['ROLE'] != 14){
                    $requestData['FILTER_TYPE']  = '';
                    $requestData['FILTER_IDS']  = '';
                }
                
                if(isset($requestData['is_edit'])){
                  $requestData['UPDATED_BY']  = Session::get('userId');
                }else{
                   $requestData['CREATED_BY']  = Session::get('userId');
                }

                unset($requestData['EMP_NAME']);
                DB::beginTransaction();
                
                if(isset($requestData['is_edit'])){
                    unset($requestData['is_edit']);
                    $oldRoleId = DB::table('USERS')->where('ID',$requestData['id'])
                                                    ->get()->toArray();
                    $oldRoleId = current($oldRoleId);
                    $empUserId = $oldRoleId->empldapuserid;
                    // echo "<pre>";print_r($requestData);exit;
                    if($empUserId != $requestData['EMPLDAPUSERID']){
                        // echo "test";exit;
                        $checkuserAlredyExist = DB::table('USERS')
                                                ->where('EMPLDAPUSERID',$requestData['EMPLDAPUSERID'])
                                                ->count();
                        if($checkuserAlredyExist>0){
                            return json_encode(['status'=>'fail','msg'=>'User already exist!','employeeDetails'=>$requestData]);
                        }
                    }
                    // echo "test2";exit;
                    $oldRoleId = $oldRoleId->role;
                    $users = DB::table('USERS')->where('ID',$requestData['id'])->update($requestData);
                    $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$requestData['UPDATED_BY'],$users,'Updated User: '.$requestData['HRMSNO'],$oldRoleId,$requestData['ROLE']);
                }else{
                    $checkuserAlredyExist = DB::table('USERS')
                                                ->where('HRMSNO',$requestData['HRMSNO'])
                                                ->count();
                    if ($checkuserAlredyExist > 0) {
                        return json_encode(['status'=>'fail','msg'=>'User already exist!','employeeDetails'=>$requestData]);
                    } 
                    $users = DB::table('USERS')->insertGetId($requestData);
                    $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$requestData['CREATED_BY'],$users,'Add User: '.$requestData['HRMSNO'],'',$requestData['ROLE']);
                
                }
                
                $requestData['requesterrole'] = Session::get('role');

                if($users){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'User Details Updated Successfully.','employeeDetails'=>$requestData]);    
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>$requestData]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getuamuserroles(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData = $request->get('data');
                $userRoles = DB::table('USER_ROLES')->pluck('role','id')->toArray();
                if(count($userRoles) > 0){
                    return json_encode(['status'=>'success','msg'=>'User Details Updated Successfully.','userRoles'=>$userRoles]);    
                }else{
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','userRoles'=>[]]);
                }
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addusertabledata(Request $request)
    {
        try {
          
            $table = 'USERS';
            $branchlists = CommonFunctions::getBranch();
            $regionlists = CommonFunctions::getRegional();
            $zonelists = CommonFunctions::getZone();
            $clusterlists = CommonFunctions::getCluster();
            $rolelists = CommonFunctions::getRoles();

            return view('uam.addusertabledata')->with('branchlists',$branchlists)
                                                ->with('regionlists',$regionlists)
                                                ->with('zonelists',$zonelists)
                                                ->with('clusterlists',$clusterlists)
                                                ->with('rolelists',$rolelists)
                                                ->with('table',$table)
                                                //->with('columns',$columns)
                                                //->with('rowDetails',$rowDetails)
                                                ;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function saveusercolumndata(Request $request)
    {
        try {
            if ($request->ajax()){
                DB::beginTransaction();
                $requestData = $request->get('data');
                $table = $requestData['table'];
                $requestData['empstatus'] = 'Y';
                $columnData = Arr::except($requestData,['table','functionName']);
                
                
                if(!isset($requestData['empsol'])){
                    return json_encode(['status'=>'fail','msg'=>'Please enter valid sold id !','data'=>[]]);
                }

                if(!isset($requestData['empldapuserid'])){
                    return json_encode(['status'=>'fail','msg'=>'Please enter valid domain id !','data'=>[]]);
                }


                $checkBranchExist = DB::table('BRANCH')->where('branch_id', $requestData['empsol'])->get()->toArray();
                if (count($checkBranchExist) == 0) {
                    return json_encode(['status'=>'warning','msg'=>'Branch not Exist!','data'=>[]]);
                }

                if(isset($requestData['rowId']))
                {
                    if (strtolower($requestData['rowId']) != strtolower($requestData['empldapuserid'])) {
                        $checkAlredyExistBranch = DB::table('USERS')
                                                    ->where(strtolower('empldapuserid'), strtolower($requestData['empldapuserid']))
                                                    ->get()->toArray();
                        if (count($checkAlredyExistBranch) > 0) {
                            return json_encode(['status'=>'warning','msg'=>'User already exist!','data'=>[]]);
                        }
                    }

                    $columnData['updated_by'] = $this->userId;
                    unset($columnData['rowId']);
                    $userOldData = DB::table('USERS')->where('empldapuserid',$requestData['rowId'])
                                                    ->get()->toArray();
                    $userOldData = current($userOldData);
                    //echo "<pre>";print_r($columnData);exit;
                    $saveColumnData = DB::table('USERS')->where('empldapuserid', $requestData['rowId'])->update($columnData);

                    $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['updated_by'],$saveColumnData,'Updated User: '.$requestData['role'],$userOldData->role,$requestData['role']);
                }else{
                    $checkAlredyExistBranch = DB::table('USERS')
                                                    ->where(strtolower('empldapuserid'), strtolower($requestData['empldapuserid']))
                                                    ->get()->toArray();
                    if (count($checkAlredyExistBranch) > 0) {
                        return json_encode(['status'=>'warning','msg'=>'User already exist!','data'=>[]]);
                    } 
                    $columnData['created_by'] = $this->userId;
                    $saveColumnData = DB::table('USERS')->insert($columnData);
                    
                    $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['created_by'],$saveColumnData,'Add User: '.$requestData['role'],'',$requestData['role']);
                }

                if($saveColumnData)
                {
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Column Details Saved Successfully.','data'=>[]]);
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function npconeDashboard(Request $request)
    {
        $userID = $request->value;
        $role = Session::get('role');
        $users = CommonFunctions::getuamusers(3);
        $customerNames = CommonFunctions::getusers();       

        $employeeDetails = DB::table('users')        
        ->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"),'ID','HRMSNO','EMPLDAPUSERID','NORMAL_FLAG','PRIORITY_FLAG','NR_FLAG')
        ->where('role',3)
        ->where('EMPSTATUS','!=','D')
        ->get()->toArray();     

        if(!empty($userID))
        {
        $employeeDetails=DB::table('users')
        ->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"),'ID','HRMSNO','EMPLDAPUSERID','NORMAL_FLAG','PRIORITY_FLAG','NR_FLAG')
        ->where('ID',$userID)
        ->where('EMPSTATUS','!=','D')
        ->get()->toArray();    
       
        return response()->json($employeeDetails);
        }
        return view('uam.uamdashboard',compact('users','employeeDetails','customerNames'));
    }
    public function npctwoDashboard(Request $request){
        $userID = $request->value;        
        $role = Session::get('role');
        $users = CommonFunctions::getuamusers(4);
        $customerNames = CommonFunctions::getusers();     
        
        $employeeDetails=DB::table('users')        
        ->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"),'ID','HRMSNO','EMPLDAPUSERID','NORMAL_FLAG','PRIORITY_FLAG','NR_FLAG')
        ->where('role',4)
        ->where('EMPSTATUS','!=','D')
        ->get()->toArray();     

        if(!empty($userID))
        {
                $employeeDetails=DB::table('users')
                ->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"),'ID','HRMSNO','EMPLDAPUSERID','NORMAL_FLAG','PRIORITY_FLAG','NR_FLAG')
        ->where('ID',$userID)
        ->where('EMPSTATUS','!=','D')
        ->get()->toArray();    
                                        
        return response()->json($employeeDetails);
            }
        return view('uam.uamdashboard',compact('users','employeeDetails','customerNames'));
            }

    public function saveUserdata(Request $request){        
   
        $requestData=$request->all();     
                $oldUserData = DB::table('users')->where('id', $request->id)
                                                 ->select('NORMAL_FLAG','PRIORITY_FLAG','NR_FLAG')
                                                 ->get();
                $oldUserData= (array) current($oldUserData);

                DB::table('users')->where('id', $request->id)
                                  ->update(['NORMAL_FLAG' => $request->normal_flag,'PRIORITY_FLAG' => $request->priority_flag,'NR_FLAG' => $request->nr_flag]); 


                $columnData['created_by'] = $this->userId;
                $old_value = json_encode($oldUserData);
                $new_value = json_encode($requestData);
                $comments='';                
                $comments = 'Updated normal_flag to '.$request->normal_flag.', priority_flag to '.$request->priority_flag.', nr_flag to '.$request->nr_flag;
                $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['created_by'],$comments,$comments,$old_value,$new_value);

                return response()->json($requestData);
	}

    public function UserUnDelete(Request $request)
    { 
        $userid= Session::get('userId');
        $lastdays=CommonFunctions::getapplicationSettingsDetails('UNDELETE_LAST_DAYS');
        $users = CommonFunctions::getdeleteuamusers($lastdays);
        $query = DB::table('USERS as usr')->select( 'usr.ID',
                                                    'usr.hrmsno',
                                                    'usr.empsol',
                                                    'usr.emp_first_name',
                                                    'usr.emp_middle_name',
                                                    'usr.emp_last_name',
                                                    'usr.empldapuserid',
                                                    'usr.empbusinessunit',
                                                    'usr.emplocation',
                                                    'usr.empbranch',
                                                    'ur.role as role',
                                                    'usr.empstatus',
            DB::raw("usr.emp_first_name || ' ' || usr.emp_middle_name || ' ' || usr.emp_last_name AS emp_name"))
            ->leftjoin('USER_ROLES as ur','ur.ID','usr.role');

            if (!empty($request->all())) {
                $tokenParams = explode('.',Cookie::get('token'));
                $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
                $id = base64_decode($decodedString);
                $employeeDetails = $query->where('usr.ID', $id)->get()->toArray();
            } else {
                $employeeDetails = $query
                    ->where('usr.updated_at', '>=', now()->subDays($lastdays))
                    ->where('usr.empstatus', 'D')
                    ->get()
                    ->toArray();
            }
            $old_value = '';
            $new_value = '';
            $columnData['created_by'] = $this->userId;
            $comments='';  
            $comments = 'User Account Undeleted';
            $addUpdateUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['created_by'],$comments,$comments,$old_value,$new_value);
         
        return view("uam.userundelete")->with('employeeDetails', $employeeDetails)
                                       ->with('users', $users);                                
    }

    public function uamundeletereport()
    {
        try{
        $data = DB::table('USER_UNDELETE as usr')
            ->select('usr.user_id',
                     'usr.hrmsno',
                     'usr.empsol',
                     'usr.emp_first_name',
                     'usr.emp_middle_name',
                     'usr.emp_last_name',
                     'usr.empldapuserid',
                     'usr.empbusinessunit',
                     'usr.emplocation',
                     'usr.empbranch',
                     'usr.role',
                     'usr.empstatus',
                     'usr.created_at',
                     'usr.created_by')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();
    
        $filename = "UndeletReport_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://output', 'w');
    
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
    
        fputcsv($handle, ['USER ID', 'HRMS NO', 'EMP FIRST NAME', 'EMP MIDDLE NAME', 'EMP LAST NAME', 'EMPSOL', 'EMP LDAP USER-ID', 'EMPBUSINESS UNIT', 'EMP LOCATION', 'EMP BRANCH', 'ROLE', 'EMP STATUS', 'CREATED AT', 'CREATED BY']);
    
        foreach ($data as $row) {
            fputcsv($handle, [
                                                $row->user_id,
                                                $row->hrmsno,
                                                $row->emp_first_name,
                                                $row->emp_middle_name,
                                                $row->emp_last_name,
                                                $row->empsol,
                                                $row->empldapuserid,
                                                $row->empbusinessunit,
                                                $row->emplocation,
                                                $row->empbranch,
                                                $row->role,
                                                $row->empstatus,
                                                $row->created_at,
                                                $row->created_by
            ]);
        }
        
        fclose($handle);
        exit;
        }catch(\Exception $e){
            return $e->getMessage();
    }
    }
}
?>