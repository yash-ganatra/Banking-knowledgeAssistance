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

class EmailSmsController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/EmailSmsController','templates','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    /*
    * Method Name: mapping
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to display template
    *
    * Input Params:
    *
    * Output:
    * Returns view template
    */
    public function templates(Request $request)
    {
        try {
            //render mapping template
            return view('admin.viewtemplates');
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function gettemplates(Request $request)
    {
        try {
            if($request->ajax())
            {
                $requestData = $request->get('data');
                $filteredColumns = ['MESSAGES.ID','MESSAGES.ACTIVITY_CODE','MESSAGES.ACTIVITY','USER_ROLES.ROLE','MESSAGES.MESSAGE_TYPE','MESSAGE',
                                                                    'MESSAGES.FUNCTION_NAME','IS_ACTIVE','ACTION'];
                $i=0;
                $array_column = [];
                foreach ($filteredColumns as $column) {
                    // if($column == "ROLE")
                    // {
                    //     $dt[$i] = array( 'db' => 'ROLE','dt' => $i,
                    //         'formatter' => function( $d, $row ) {
                    //             $html = config('constants.ROLES_IDS')[$row->role];
                    //             return $html;
                    //         }
                    //     );
                    // }
                  if($column == "IS_ACTIVE"){
                        array_push($array_column,"MESSAGES.IS_ACTIVE");
                        $dt[$i] = array( 'db' => strtolower('MESSAGES.IS_ACTIVE'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                if($row->is_active == 1){
                                    $html = 'Yes';
                                }else{
                                    $html = 'No';
                                }
                                return $html;
                            }
                        );
                    }
                    else if($column == "MESSAGE"){
                        array_push($array_column,"MESSAGES.MESSAGE");
                        $dt[$i] = array( 'db' => strtolower('MESSAGES.MESSAGE'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                return substr(str_replace('<br>', '_NL_', $row->message), 0,50);
                               
                            }
                        );
                    }
                    else if($column == "ACTION"){
                        array_push($array_column,DB::raw("MESSAGES.ID AS action"));
                        $dt[$i] = array( 'db' => 'action','dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = '';
                                $html .= '<div class="tabledit-toolbar btn-toolbar" style="text-align: left;">';
                                    $html .= '<div class="btn-group btn-group-sm" style="float: none;">';
                                            $html .= '<button type="button" class="tabledit-edit-button btn btn-primary waves-effect 
                                                            waves-light new-btn edit_template" id="'.$row->id.'" style="float: none;margin: 5px;">';
                                            $html .= '<span class="icofont icofont-ui-edit" ></span>';
                                        $html .= '</button>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                return $html;
                            }
                        );
                    }else{
                        array_push($array_column,"$column");
                        $dt[$i]['label'] = $column;
                        $dt[$i]['db'] = strtolower($column);
                        $dt[$i]['dt'] = $i;    
                    }                
                    $i++;              
                }

                // $dt_obj = (new SSP('MESSAGES', $dt)); old 
                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);
                $dt_obj = DB::table('MESSAGES')->select($array_column);
                $dt_obj = $dt_obj->leftJoin('USER_ROLES','USER_ROLES.ID','MESSAGES.ROLE');

                $dt_obj = $dt_obj->orderBy('ID', 'DESC');
                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();

                $dd["items"] = (array) $dd["items"];
                $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
           
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
                // return response()->json($dt_obj->getDtArr()); old
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public function addtemplate(Request $request)
    {
        try {
            $users = CommonFunctions::getusers();
            //render mapping template
            $activity = config('constants.NOTIFICATION_ACTIVITIES');

            return view('admin.addtemplate')->with('activity',$activity);
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
        }
    }


    public function savetemplate(Request $request)
    {
        try {
            if($request->ajax())
            {
                $requestData = Arr::except($request->get('data'),'functionName');

                $activity = config('constants.NOTIFICATION_ACTIVITIES');

                // if(isset($requestData['ACTIVITY'])){

                //     $requestData['ACTIVITY'] = config('constants.NOTIFICATION_ACTIVITIES')[$requestData['ACTIVITY']];
                // }
                 

                if(isset($requestData['MESSAGE'])){

                    $requestData['MESSAGE'] = str_replace('_NL_', '<br>',$requestData['MESSAGE']);
                }

                //echo "<pre>";print_r($requestData['MESSAGE']);exit();
                // if(isset($requestData['ACTIVITY_CODE'])){
                //     //assign search param value
                //     $searchParam = $requestData['ACTIVITY_CODE'];
                // }
                // if(isset($requestData['ACTIVITY'])){
                //     //assign search param value
                //     $searchParam = $requestData['ACTIVITY'];

                //     // if(CommonFunctions::isDangerous($searchParam,'INPUT')){
                //     //      return json_encode(['status'=>'fail','msg'=>'Error! Invalid Input','data'=>[]]);
                //     // }
                //     // else{
                //     //     $searchParam = CommonFunctions::inputRegexValidation($searchParam,'INPUT');
                //     // }
                // }
                // if(isset($requestData['SUBJECT'])){
                //     //assign search param value
                //     $searchParam = $requestData['SUBJECT'];
                //     if(CommonFunctions::isDangerous($searchParam,'INPUT')){
                //          return json_encode(['status'=>'fail','msg'=>'Error! Invalid Input','data'=>[]]);
                //     }
                //     else{
                //         $searchParam = CommonFunctions::inputRegexValidation($searchParam,'INPUT');
                //     }
                // }
                // if(isset($requestData['MESSAGE'])){
                //     //assign search param value
                //     $searchParam = $requestData['MESSAGE'];
                // }

                // if(isset($requestData['FUNCTION_NAME'])){
                //     //assign search param value
                //     $searchParam = $requestData['FUNCTION_NAME'];
                // }
              
               //echo "<pre>";print_r($requestData);exit;
                DB::beginTransaction();
                if(isset($requestData['id'])){
                    $message = DB::table('MESSAGES')->where('ID',$requestData['id'])->update($requestData);
                }else{
                    $message = DB::table('MESSAGES')->insert($requestData);
                }                
                if($message){
                    DB::commit();
                    return json_encode(['status'=>'success','msg'=>'Message Template Saved Successfully.','data'=>[]]);    
                }else{
                    DB::rollback();
                    return json_encode(['status'=>'warning','msg'=>'Error! Please try again later.','employeeDetails'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function edittemplate(Request $request)
    {
        try {
            $activityDetails = array();
            $tokenParams = explode('.',Cookie::get('token'));
            //decode string
            $decodedString = CommonFunctions::decrypt256($request->all()['encodedString'],$tokenParams[2]);
            $templateId = base64_decode($decodedString);
            $activity = config('constants.NOTIFICATION_ACTIVITIES');
            $activityDetails = DB::table('MESSAGES')
                                                    ->where('ID',$templateId)
                                                     ->get()->toArray();

            if(count($activityDetails) > 0)
            {
                $activityDetails = (array) current($activityDetails);
            }

            if(isset($activityDetails['message'])){

                $activityDetails['message'] = str_replace('<br>', '_NL_', $activityDetails['message']);

            }

            //render mapping template
            return view('admin.edittemplate')
                                            ->with('activity',$activity)
                                            ->with('activityDetails',$activityDetails)
                                                ;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

}
?>