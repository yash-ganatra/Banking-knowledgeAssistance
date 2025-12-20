<?php

namespace App\Http\Controllers\ChannelId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use App\Helpers\OaoCommonFunctions	;
use App\Helpers\Api;
use Illuminate\Support\Arr;
use Session;
use Cookie;
use Crypt; 
use Carbon\Carbon;
use DB;
use Exception;

class DsaLogsController extends Controller
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
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if(!in_array($this->roleId,[18])){

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
                $saveuserlog = CommonFunctions::createUserLogDirect('ChannelId/DsaLogsController','dsalog','Unauthorized attempt detected by '.$this->userId,'','','1');

                   header('Refresh: 5; URL= ../login');
                 die();
            }

        }
    }


     public function apilog()
    {
       $tables =  ['1'=>'ENCRYPTED_API_SERVICE_LOG',
                   '2' => 'DSA_ACTIVITY_LOG',
                   '3' => 'VIDEO_EKYC_RESPONSE',
                   '4' => 'EMAIL_SMS_MESSAGES'
                  ];

       return view('channelid.ApiLogTable')->with('tables',$tables);
    }

     public function getapilogdata(Request $request)
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
            $html .= '</tr>';
            $html .= '</thead>';
            $html .=  '<tbody>';
            $html .= 	'<tr>';        
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



}

?>