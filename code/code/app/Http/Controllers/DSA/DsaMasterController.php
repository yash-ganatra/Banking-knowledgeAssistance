<?php

namespace App\Http\Controllers\DSA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use App\Helpers\OaoCommonFunctions  ;
use App\Helpers\Api;
use Illuminate\Support\Arr;
use Session;
use Cookie;
use Crypt; 
use Carbon\Carbon;
use DB;
use Exception;

class DsaMasterController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/DSA/DsaMasterController','dsamastertables','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    public function dsamastertable()
    {
       $tables =  ['1'=>'BRANCH','2'=>'FIN_PCS_DESC'];

       
       return view('dsa.oaotable')->with('tables',$tables);
    }


    public function getoaocolumnsdata(Request $request)
    {
      try{
        if ($request->ajax()){
                $requestData = $request->get('data');

            $tableName = $requestData['table_name'];
            $table = 'OAO.'.$tableName;
            $getColumnsData = DB::table('all_tab_columns')->where('table_name', $tableName)->where('owner','OAO')->pluck('column_name')->toArray();
            $getColumnsData = array_flip($getColumnsData); 
            $filteredColumns = Arr::except($getColumnsData, ['ID','CREATED_BY','CREATED_AT','UPDATED_BY','UPDATED_AT']);
            $filteredColumns = array_flip($filteredColumns);
            $i=0;
            $maxLength = 50;
            $getColumnsValues = DB::table($table)->take(500)->get()->toArray();
            

            $html = '';
            $html .= '<thead>';
            $html .= '<tr>';
            foreach($filteredColumns as $filteredColumn){
                $html .= '<th>'.$filteredColumn.'</th>';
            }
                $html .= '<th>ACTION</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .=  '<tbody>';
            $html .=    '<tr>';        
            foreach($getColumnsValues as $getData){ 
                                     
                  $values = get_object_vars($getColumnsValues[$i]);
                  $filteredColumns = Arr::except($values, ['rn','id','created_by','created_at','updated_by','updated_at']);
                  $values = array_values($filteredColumns);
                  $i++;
            
         foreach($values as $value){

            if(strlen($value) > $maxLength){
                $html .= '<td>';
                $html .= "<span class='mytooltip'>";
                $html .= "<span type='button' class='tooltipp oao_modal' data-title='<pre>$value</pre>' data-type='Service Request' data-toggle='modal' data-target='#oao_modal'>";
                $html .= substr($value, 0,50);
                $html .= "</span>";
                $html .= "</span>";
                $html .= "</td>";
            }else{
                $html .= '<td>'.$value.'</td>';
            }

         }
            if($tableName != 'ENCRYPTED_API_SERVICE_LOG'){
              $html .= '<td><a href="javascript:void(0)" id='.$values[0].' class="edit_oao_table">Edit</a></td>';
            }
              $html .= '</tr>';
         }
           $html.=  '</tbody>';
           $html .= '</table>';
           // $html .=  '<div>'.$getColumnsValues->links().'</div>;

        return json_encode(['status'=>'success','msg'=>'Selected fetched','data'=>$html,'table'=>$tableName]);

          }
        }catch(\Illuminate\Database\QueryException $e){
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        
        }
    }

    public function addoaocolumndata(Request $request)
    {
        try{
  
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $requestData = explode('.',base64_decode($decodedString));
            $table = $requestData[0];
            $columnDatas = '';
            $branchlists = '';
            $regionlists = '';
            $zonelists = '';
            $clusterlists = '';
            $rowId = '';
            $tableName = 'OAO.'.$table;
        
            if($table == 'BRANCH'){
                $branchlists = OaoCommonFunctions::getBranch();
                $regionlists = OaoCommonFunctions::getRegional();
                $zonelists = OaoCommonFunctions::getZone();
                $clusterlists = OaoCommonFunctions::getCluster();
                if(isset($requestData[1]))
                {
                    $rowId = $requestData[1];
                    $columnDatas = DB::table($tableName)->where('BRANCH_ID',$rowId)->get()->toArray();
                    $columnDatas = (array) current($columnDatas);
                }
            }else{
                    if(isset($requestData[1])){
                        $rowId = $requestData[1];
                        $columnDatas = DB::table($table)->where('PINCODE',$rowId)->get()->toArray();
                        $columnDatas = (array) current($columnDatas);
                        $columnDatas =  Arr::except($columnDatas,['id','created_by','created_at','updated_by','updated_at']);

                    }else{
                        $columnDatas = DB::table($table)->get()->toArray();
                        $columnDatas = (array) current($columnDatas);
                        $columnDatas =  Arr::except($columnDatas,['id','created_by','created_at','updated_by','updated_at']);
                    }

            }


            return view('dsa.addoaotable')->with('branchlists',$branchlists)
                                                ->with('regionlists',$regionlists)
                                                ->with('zonelists',$zonelists)
                                                ->with('clusterlists',$clusterlists)
                                                ->with('columnDatas',$columnDatas)
                                                ->with('rowId',$rowId)
                                                ->with('table',$table);
             
         }catch(\Illuminate\Database\QueryException $e){
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        
        }

    }

    public function saveoaocolumndata(Request $request)
    {
        try{
            if ($request->ajax()){
            $requestData = $request->get('data');
            $tableName = 'OAO.'.$requestData['table'];
        // echo "<pre>";print_r($requestData);exit;
                
                $branchData = Arr::except($requestData, ['table','functionName','rowId']);

                if(isset($requestData['rowId'])){
                    $branchData['UPDATED_BY'] = $this->userId;
                    $branchData['UPDATED_AT'] = CommonFunctions::getCurrentDBtime();

                    if($tableName == 'OAO.BRANCH'){
                        $masterColumnData = DB::table($tableName)->where('BRANCH_ID',$requestData['rowId'])->get()->toArray();
                        $saveData = DB::table($tableName)->where('BRANCH_ID',$requestData['rowId'])->update($branchData);
                    }else{
                        $masterColumnData = DB::table($tableName)->where('PINCODE',$requestData['rowId'])->get()->toArray();
                        $saveData = DB::table($tableName)->where('PINCODE',$requestData['rowId'])->update($branchData);
                    }

                    $masterColumnData = json_decode(json_encode(current($masterColumnData)), true); 
                    $newDifference = array_diff($branchData, $masterColumnData);
                    $newDifference = Arr::except($newDifference,['UPDATED_BY','UPDATED_AT']);
                    $newDifference = json_encode($newDifference);

                    $oldValues = array_diff($masterColumnData,$branchData);
                    $oldValues = Arr::except($oldValues,['created_at','updated_at', 'created_by','updated_by']);
                    $oldValues = json_encode($oldValues);

                    $jsoncolumnData = json_encode($branchData);

                    $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$branchData['UPDATED_BY'],$saveData,$tableName." Updated ID: ". $requestData['rowId'], $oldValues, $newDifference);
                }else{
                    $branchData['CREATED_BY'] = $this->userId;
                    $branchData['CREATED_AT'] = CommonFunctions::getCurrentDBtime();
                    $saveData = DB::table($tableName)->insert($branchData);
                }


            if($saveData){
                return json_encode(['status'=>'success','msg'=>'Saved Data Successfully','data'=>[]]);
            }

            // if(isset($requestData['branch_id') && $requestData['branch_id'] != ''){
            //     return json_encode(['status'=>'fail','msg'=>'Kindly','data'=>[]]);
            // }

        }
        }catch(\Illuminate\Database\QueryException $e){
            dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        
        }
    }
}
?>