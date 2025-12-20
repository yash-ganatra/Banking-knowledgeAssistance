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
use Exception;

class MasterTableController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/MasterTableController','mastertables','Unauthorized attempt detected by '.$this->userId,'','','1');

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
     * Method Name: mastertables
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
    public function mastertables()
    {
        //returns tempalte
        $tables = config('constants.TABLES');
        $tableColumnsFilter = DB::table('MASTER_TABLE_CONTROL')->where('OPTION','FL')
                                                            ->pluck('field_name','table_name')->toArray();
        // echo "<pre>";print_r($tableColumnsFilter);exit;
        return view('admin.mastertable')->with('tables',$tables)
                                        ->with('tableColumnsFilter',$tableColumnsFilter);
    }

    public function getcolumnsbytable(Request $request)
    {
        try {
            if ($request->ajax()){
                $requestData = $request->get('data');
                $table = $requestData['table_name'];
                $filteredColumns = CommonFunctions::getCoulmnsByTable($table);
                return json_encode(['status'=>'success','table'=>$table,'columns'=>array_values($filteredColumns)]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function mastertabledata(Request $request)
    {
        try {
            if ($request->ajax()){
                if($request['length'] == -1)
                {
                    $this->maxLength = 500;
                }
                $requestData = $request->get('data');
                $table = strtoupper($requestData['table']);
                $array_column = [];
                $filteredColumns = CommonFunctions::getCoulmnsByTable($table);
                if(count($filteredColumns) > 0)
                {
                    $dt = array();
                    $i=0;
                    foreach ($filteredColumns as $column)
                    {   
                        if($column == "IS_ACTIVE"){
                            array_push($array_column,strtolower($table.'.IS_ACTIVE'));
                            $dt[$i] = array('db' => strtolower($table.'.IS_ACTIVE'),'dt' => $i,
                                'formatter' => function( $d, $row ){
                                    if($d==1)
                                        return 'YES';
                                    else
                                        return 'NO';
                                }
                            );
                        }
                        elseif($column == "ACTION")
                        {
                            array_push($array_column,DB::raw('ID AS action'));
                            $dt[$i] = array( 'db' => DB::raw('ID AS action'),'dt' => $i,
                                'formatter' => function( $d, $row ) {
                                        $html = '<a href="javascript:void(0)" id="'.$row->id.'" class="editTableData">Edit</a>';
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
                    // $dt_obj = (new SSP($table, $dt));
                    $dt_ssp_obj = new SSP();
                    $dt_ssp_obj->setColumns($dt);

                    $dt_obj = DB::table($table)->select($array_column);
                        // echo "<pre>";print_r($requestData);exit;
                    if (isset($requestData['filterColumns'])) {
                        foreach ($requestData['filterColumns'] as $filter => $value) {
                            if($value != '')
                            {
                                $dt_obj = $dt_obj->where($filter,  'like', '%'.strtoupper($value).'%');
                            }
                        }
                    }

                    $dt_ssp_obj->setQuery($dt_obj);
                    $dd = $dt_ssp_obj->getData();

                    $dd["items"] = (array) $dd["items"];
                    $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
                
                    return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
                    // return response()->json($dt_obj->getDtArr());
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function branchtabledata(Request $request)
    {
        try {
            if ($request->ajax()){
                if($request['length'] == -1)
                {
                    $this->maxLength = 500;
                }
                $array_column = [];
                $requestData = $request->get('data');
                $table = strtoupper($requestData['table']);
                $filteredColumns = CommonFunctions::getCoulmnsByTable($table);
                if(count($filteredColumns) > 0)
                {
                    $dt = array();
                    $i=0;
                    foreach ($filteredColumns as $column)
                    {
                        if($column == "ACTION")
                        {
                            array_push($array_column,DB::raw('BRANCH_ID AS action'));
                            $dt[$i] = array( 'db' => DB::raw('BRANCH_ID AS action'),'dt' => $i,
                                'formatter' => function( $d, $row ) {
                                    $html = '<a href="javascript:void(0)" id="'.$row->branch_id.'" class="editTableData">Edit</a>';
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
                    // $dt_obj = (new SSP($table, $dt));

                    $dt_ssp_obj = new SSP();
                    $dt_ssp_obj->setColumns($dt);
                    $dt_obj = DB::table($table)->select($array_column);

                    if(isset($requestData['branch_id']) && $requestData['branch_id'] != '')
                    {
                        $dt_obj = $dt_obj->where('BRANCH_ID',  'like', '%'.$requestData['branch_id'].'%');
                    }

                    $dt_ssp_obj->setQuery($dt_obj);
                    $dd = $dt_ssp_obj->getData();

                    $dd["items"] = (array) $dd["items"];
                    $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
                
                    return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);

                    // return response()->json($dt_obj->getDtArr());
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addtabledata(Request $request)
    {
        try {
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $requestData = explode('.',base64_decode($decodedString));
            $table = $requestData[0];
            $rowDetails = array();
            if(isset($requestData[1]))
            {
                $rowId = $requestData[1];
                $getRowDetails = DB::table($table)->whereId($rowId)->get()->toArray();
                $rowDetails = (array) current($getRowDetails);
            }
            $columns = CommonFunctions::getCoulmnsByTable($table,true);
            $ro_columns = CommonFunctions::getROcoulmnsByTable($table,true);
            return view('admin.addtabledata')->with('table',$table)
                                                ->with('columns',$columns)
                                                ->with('rowDetails',$rowDetails)
                                                ->with('ro_columns',$ro_columns);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function addbranchtabledata(Request $request)
    {
        try {
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $requestData = explode('.',base64_decode($decodedString));
            $table = $requestData[0];
            $rowDetails = array();
            if(isset($requestData[1]))
            {
                $rowId = $requestData[1];
                $getRowDetails = DB::table($table)->where('branch_id', $rowId)->get()->toArray();
                $rowDetails = (array) current($getRowDetails);
            }
            $columns = CommonFunctions::getCoulmnsByTable($table,true);

            $branchlists = CommonFunctions::getBranch();
            $regionlists = CommonFunctions::getRegional();
            $zonelists = CommonFunctions::getZone();
            $clusterlists = CommonFunctions::getCluster();
            $rolelists = CommonFunctions::getRoles();

            return view('admin.addbranchtabledata')->with('branchlists',$branchlists)
                                                ->with('regionlists',$regionlists)
                                                ->with('zonelists',$zonelists)
                                                ->with('clusterlists',$clusterlists)
                                                ->with('rolelists',$rolelists)
                                                ->with('table',$table)
                                                ->with('columns',$columns)
                                                ->with('rowDetails',$rowDetails);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function savecolumndata(Request $request)
    {
        try {
            if ($request->ajax()){
                DB::beginTransaction();
                $requestData = $request->get('data');
                $table = $requestData['table'];
                $columnData = Arr::except($requestData,['table','functionName']);
                if(isset($requestData['rowId']))
                {
                    $columnData['UPDATED_BY'] = $this->userId;
                    $columnData['UPDATED_AT'] = CommonFunctions::getCurrentDBtime();
                    $masterColumnData = DB::table($table)->where('ID',$requestData['rowId'])->get()->toArray();
					$masterColumnData = json_decode(json_encode(current($masterColumnData)), true); 
					$newDifference = array_diff($columnData, $masterColumnData);
               		$newDifference = Arr::except($newDifference,['UPDATED_BY','UPDATED_AT']);
                    $newDifference = json_encode($newDifference);

                    $oldValues = array_diff($masterColumnData,$columnData);
					$oldValues = Arr::except($oldValues,['created_at','updated_at', 'created_by','updated_by']);
                    $oldValues = json_encode($oldValues);


                    unset($columnData['rowId']);
                    $saveColumnData = DB::table($table)->whereId($requestData['rowId'])->update($columnData);
                    $jsoncolumnData = json_encode($columnData);

                    // echo "<pre>";print_r($oldValues);
                    // echo "<pre>";print_r($newDifference);exit;
                    $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['UPDATED_BY'],$saveColumnData,$table." Updated ID: ". $requestData['rowId'], $oldValues, $newDifference); 

                }else{
                    
                    $columnData['CREATED_BY'] = $this->userId;
                    $columnData['CREATED_AT'] = CommonFunctions::getCurrentDBtime();

                    $getTableId = DB::table($table)->max('id');
                    if($table != 'FIN_PCS_DESC'){
                        $columnData['ID'] = $getTableId + 1;
                    }
                    if(in_array($table,['UTR_CHECK'])){
                        if (preg_match('/[^0-9A-Z]/i', $columnData['UTR_NUMBER'])) {
                            return json_encode(['status' => 'warning', 'msg' => 'Please enter valid UTR number']);
                        }
                        $getstatus = CommonFunctions::precheckexstingadddata($table,'UTR_NUMBER',$columnData['UTR_NUMBER']);
                        if($getstatus['status'] == 'fail'){
                            return json_encode(['status'=>'warning','msg'=>$getstatus['message'],'data'=>[]]);
                        }
                    }

                    $saveColumnData = DB::table($table)->insertGetId($columnData);
                    $jsoncolumnData = json_encode($columnData);
                    // echo "<pre>";print_r($saveColumnData);exit;

                    $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['CREATED_BY'],$saveColumnData,$table." Added", '', "ID: ".$saveColumnData." added");

                }

                if($saveColumnData){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Column Details Saved Successfully.','data'=>[]]);
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','data'=>[]]);
                }
            }
        }catch(Exception $e) {
            $message = $e->getMessage();
            if (str_starts_with($message, 'Error Code')) {
                CommonFunctions::addExceptionLog($message, $request);
                $message = strstr($message,'Error Message');
                $message = substr($message,0,55);
                return json_encode(['status'=>'fail','msg'=>$message,'data'=>[$message]]);
            }
            // old code below
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function savebranchcolumndata(Request $request)
    {
        try {
            if ($request->ajax()){
                DB::beginTransaction();
                $requestData = $request->get('data');
                $table = $requestData['table'];
                $columnData = Arr::except($requestData,['table','functionName']);
                if(isset($requestData['rowId']))
                {
                    if ($requestData['rowId'] != $requestData['branch_id']) {
                        return json_encode(['status'=>'warning','msg'=>'Branch Id update not permitted.','data'=>[]]);
                    }

                    $columnData['UPDATED_AT'] = CommonFunctions::getCurrentDBtime();
                    $columnData['UPDATED_BY'] = $this->userId;

                    $masterColumnData = DB::table($table)->where('BRANCH_ID',$requestData['rowId'])->get()->toArray();
                    $masterColumnData = json_decode(json_encode(current($masterColumnData)), true); 
					$newDifference = array_diff($columnData, $masterColumnData);
               		$newDifference = Arr::except($newDifference,['UPDATED_BY','UPDATED_AT']);
                    $newDifference = json_encode($newDifference);

                    $oldValues = array_diff($masterColumnData,$columnData);
					$oldValues = Arr::except($oldValues,['created_at','updated_at', 'created_by','updated_by','city_id','state_id','city_code','city','state','metro_urban','dp_code','state_code']);
                    $oldValues = json_encode($oldValues);

                    unset($columnData['rowId']);
                    $saveColumnData = DB::table($table)->where('BRANCH_ID', $requestData['rowId'])->update($columnData);

                    $jsoncolumnData = json_encode($columnData);


                    $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['UPDATED_BY'],$saveColumnData,"Branch Id:".$requestData['rowId']." Updated",$oldValues, $newDifference);

                }else{
                    $checkAlredyExistBranch = DB::table($table)
                                                    ->where('branch_id', $requestData['branch_id'])
                                                    ->get()->toArray();
                    if (count($checkAlredyExistBranch) > 0) {
                        return json_encode(['status'=>'warning','msg'=>'Branch Id already exist!','data'=>[]]);
                    }

                    $columnData['CREATED_AT'] = CommonFunctions::getCurrentDBtime();
                    $columnData['CREATED_BY'] = $this->userId;
                    $saveColumnData = DB::table($table)->insert($columnData);
                    // echo "<pre>";print_r($columnData);exit;
                    $jsoncolumnData = json_encode($columnData['branch_id']);

                    $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$columnData['CREATED_BY'],$saveColumnData,"Branch Added", '', "ID: ".$jsoncolumnData." added");

                }

                if($saveColumnData){
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
}
?>
