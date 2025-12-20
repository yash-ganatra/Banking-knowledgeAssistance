<?php

namespace App\Http\Controllers\Archival;

use App\Helpers\CommonFunctions;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use SoulDoit\DataTable\SSP;
use Illuminate\Http\Request;
use Cookie;
use Crypt,Cache,Session;	
use File;
use Illuminate\Support\Facades\DB;


class ArchivalRecordController extends Controller
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
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if(!in_array($this->roleId,[9])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('Archival/ArchivalRecordController','ArchivalRecordController','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
        ini_set('max_execution_time', '130');
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

  	public function getarchivalrecord()
  	{
  		return view('archival.archivalrecord');
  	}

  	public function importarchivalrecord(Request $request)
  	{
      $requestData = $request->get('data');
		  if(isset($requestData['excel_data']) && $requestData['excel_data'] != ''){
  			$excelData = json_decode($requestData['excel_data'], true);
        $duplicateRecords = array();
        $failedRows = array();
        $success = 0;        
			  for ($i=1; $i < count($excelData); $i++){ 
          try{
            if($excelData[$i][0]=='' || $excelData[$i][1]=='' || $excelData[$i][2]=='' || $excelData[$i][3]=='' || $excelData[$i][7]=='' || $excelData[$i][8]==''){              
              array_push($failedRows,$i);
              continue;
            }

              $accountOpeningDate = trim(preg_replace('/\s*\([^)]*\)/', '', $excelData[$i][5]));
              $accountOpeningDate = Carbon::parse($accountOpeningDate)->format('Y-m-d');

              $archivalDate = trim(preg_replace('/\s*\([^)]*\)/', '', $excelData[$i][8]));
              $archivalDate = Carbon::parse($archivalDate)->format('Y-m-d');
              if($excelData[$i][5] == ''){
                $accountOpeningDate = '';
              }
              if($excelData[$i][8] == ''){
                $archivalDate = '';
              }

              $insertData = [	'BOX_BARCODE' => $excelData[$i][0],
                              'FILE_BARCODE' => $excelData[$i][1],
                              'AOF_NUMBER' => $excelData[$i][2],
                              'CUSTOMER_NAME' => $excelData[$i][3],
                              'CUSTOMER_ID' => $excelData[$i][4],
                              'ACCOUNT_OPENING_DATE' => $accountOpeningDate,
                              'ACCOUNT_ID' => $excelData[$i][6],
                              'BRANCH_ID' => $excelData[$i][7],
                              'ARCHIVAL_DATE' => $archivalDate,
                              'UPDATED_BY' => Session::get('userId'),
                              'UPDATED_AT' => Carbon::now()
                          ];

                $msg = 'Records Updated';              
                $checkMutipleRecords = DB::table('ARCHIVAL_RECORDS')->where(function ($query) use($insertData){
                                                                      $query->where('AOF_NUMBER',$insertData['AOF_NUMBER'])
                                                                      ->orWhere('FILE_BARCODE',$insertData['FILE_BARCODE']);
                                                                    })->get()->toArray();
                if(count($checkMutipleRecords) >= 1){
                  $insertData = Arr::except($insertData,['UPDATED_BY','UPDATED_AT']);
                  $insertData = array_values($insertData);
                  array_push($duplicateRecords,$insertData);
                }else{
                    $insertData['CREATED_BY'] = Session::get('userId');
                    $updateRecord = DB::table('ARCHIVAL_RECORDS')->insert($insertData);
                    if($updateRecord){
                      DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$insertData['AOF_NUMBER'])->update(['PHYSICAL_FORM_STATUS' => 21]);
                    }
                    $success++;
                }
          }catch(\Exception $e){
            if(count($failedRows)<10){
              array_push($failedRows,$i);
              //echo 'Failed: '.$i;
            }  
            continue;
          }
			  }

          if(!empty($duplicateRecords) || !empty($failedRows)){
            $msg = $success.' records imported! ';
            if(count($duplicateRecords)>0){
              $msg .= count($duplicateRecords).' duplicate records found.'; 
            }  
            if(count($failedRows)>0){
              $msg .= ' First 10 failed/error rows: '.implode(', ', $failedRows).' ...';
            }
            $html = '';
            for($i=0; $i < count($duplicateRecords); $i++){
                $html .= '<tr>';
            for($j=0; $j <= 8; $j++){
                $html .= '<td>'.$duplicateRecords[$i][$j].'</td>'; 
            } 
                $html .= '</tr>';
            }
            return json_encode(['status'=>'warning','msg'=>$msg,'data'=>['duplicateRecords' => $html]]);
          }else{
            return json_encode(['status'=>'success','msg'=>$msg,'data'=>[]]);

          }
	     }
      } 

    public function allarchivalrecord(){
      return view('archival.allarchivalrecord');
    }


		public function archivalrecords(Request $request)
		{
        $requestData = $request->get('data');
        $select_arr = [];
  			$filteredColumns = ['BOX_BARCODE','FILE_BARCODE','AOF_NUMBER','CUSTOMER_NAME','CUSTOMER_ID','ACCOUNT_OPENING_DATE','ACCOUNT_ID','BRANCH_ID','ARCHIVAL_DATE'];
  			$i = 0;
      	foreach ($filteredColumns as $column) {
            if($column == "AOF_NUMBER"){
                array_push($select_arr, strtolower('ARCHIVAL_RECORDS.AOF_NUMBER'));
                $dt[$i] = array( 'db' => strtolower('ARCHIVAL_RECORDS.AOF_NUMBER'),'dt' => $i,
                          'formatter' => function( $d, $row ) {
                                  $html = '<a href=""  style="font-size:15px;" class="archivalrecords" >'.$row->aof_number.'</a>';   
                  return $html;
                });
          	}else{
          		array_push($select_arr, $column);
              $dt[$i]['label'] = $column;
              $dt[$i]['db'] = strtolower($column);
              $dt[$i]['dt'] = $i;
            }
              $i++;              
        } 
      	//$dt_obj = new SSP('ARCHIVAL_RECORDS', $dt); commented line dusring version upgrade
        $dt_ssp_obj = new SSP();
        $dt_ssp_obj->setColumns($dt);
        $dt_obj = DB::table('ARCHIVAL_RECORDS')->select($select_arr);
        if(isset($requestData['AOF_NUMBER']) && $requestData['AOF_NUMBER'] != '')
        {
          $dt_obj = $dt_obj->where('ARCHIVAL_RECORDS.AOF_NUMBER', 'like', '%'.$requestData['AOF_NUMBER'].'%');
        }

        $dt_ssp_obj->setQuery($dt_obj);
        $dd = $dt_ssp_obj->getData();
        $dd['items'] = (array) $dd['items'];
        $dd['items'] = array_map(fn($items) => array_values((array) $items), $dd['items']);

        return response()->json(['draw' => 1, 'recordsTotal' => "$dd[total_item_count]", 'recordsFiltered' => "$dd[total_filtered_item_count]", 'data' => $dd['items']]);
      	//return response()->json($dt_obj->getDtArr());
    }

    public function editarchivalexcelrecord(Request $request){
        $tokenParams = explode('.',Cookie::get('token'));
        $getAofData = '';
        $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
        $requestData = explode('.',base64_decode($decodedString));
        if(isset($requestData[0]) && $requestData[0]){
          $getAofData = DB::table('ARCHIVAL_RECORDS')->where('AOF_NUMBER',$requestData[0])->get()->toArray();
          $getAofData = (array) $getAofData;
          if(count($getAofData) == 0){
            $getAofData = DB::table('ARCHIVAL_RECORDS')->get()->toArray();
            $getAofData = (array) current($getAofData);
            $getAofData = array_keys($getAofData);
            $getAofData = array_fill_keys($getAofData, '');       
            $getAofData['aof_number'] = $requestData[0];
          }else{
            $getAofData = (array) current($getAofData);
          }

        }
        return view('archival.editarchivalexcelrecord')->with('getAofData',$getAofData)->with('previousPage', $requestData[1]);
    }

    public function savearchivalrecord(Request $request){
      $requestData = $request->get('data');
      $requestData = Arr::except($requestData, ['functionName']);
      $table = 'ARCHIVAL_RECORDS';

      $requestData['UPDATED_BY'] = $this->userId;
      $requestData['UPDATED_AT'] = CommonFunctions::getCurrentDBtime();
      $masterArchivalExcelData = DB::table($table)->where('AOF_NUMBER',$requestData['aof_number'])->get()->toArray();
      $checkifExists = count($masterArchivalExcelData);
      $masterArchivalExcelData = json_decode(json_encode(current($masterArchivalExcelData)), true); 

      if($checkifExists < 1){
        $saveColumnData = DB::table($table)->insert($requestData);
      }else{
        $newDifference = array_diff($requestData, $masterArchivalExcelData);
        $newDifference = Arr::except($newDifference,['UPDATED_BY','UPDATED_AT']);
        $newDifference = json_encode($newDifference);

        $oldValues = array_diff($masterArchivalExcelData,$requestData);
        $oldValues = Arr::except($oldValues,['created_at','updated_at', 'created_by','updated_by']);
        $oldValues = json_encode($oldValues);
        $saveColumnData = DB::table($table)->where('AOF_NUMBER',$requestData['aof_number'])->update($requestData);
        $saveAddEditUserLog = CommonFunctions::createAddEditUserLog($request,$requestData['UPDATED_BY'],$saveColumnData,$table." Updated ID: ". $requestData['aof_number'], $oldValues, $newDifference);
      }

      if($saveColumnData){
          DB::commit();
          return json_encode(['status'=>'success','msg'=>'Column Details Saved Successfully.','data'=>[]]);
      }else{
          DB::rollback();
          return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','data'=>[]]);
      }
  }
}

