<?php

namespace App\Http\Controllers\ChannelId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use DB;
use Cookie;
use Crypt;
use Illuminate\Support\Arr;
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
                    font-weight: 500;'>Unauthorized attempt detected.<br>Event logged for ChannelId team.</p>
                 
                  </div>";
                $saveuserlog = CommonFunctions::createUserLogDirect('ChannelId/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                   header('Refresh: 5; URL= ../login');
                 die();
            }
                    }
    }

    public function templates(Request $request)
    {
        try {
            //render mapping template
            $gettemplates = DB::table('OAO.MESSAGES')->get()->toArray();
            $html = "<tbody>";
            foreach($gettemplates as $template){
                $html .= "<tr>";
                $template = (array)($template);
                $template = Arr::except($template, ['created_at', 'created_by', 'subject']);
                foreach($template as $value){
                    $html .= "<td>" . $value ."</td>";
                }
                    $html .= '<td><div class="tabledit-toolbar btn-toolbar" style="text-align: left;">';
                                    $html .= '<div class="btn-group btn-group-sm" style="float: none;">';
                                            $html .= '<button type="button" class="tabledit-edit-button btn btn-primary waves-effect 
                                                            waves-light new-btn edit_template" id="'.$template['id'].'" style="float: none;margin: 5px;">';
                                            $html .= '<span class="icofont icofont-ui-edit" ></span>';
                                        $html .= '</button>';
                                    $html .= '</div>';
                                $html .= '</div></td>';
                $html .= "</tr>";
            }
            $html.= "</tbody>";
            return view('channelid.emailsmstemplate')->with('html', $html);
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
            $activity = ['AUTH_DONE' =>'AUTH_DONE', 'PAN_DONE'=>'PAN_DONE', 'CIDD_DONE'=>'CIDD_DONE', 'NOMINATION_DONE'=>'NOMINATION_DONE', 'FUNDING_INITIATED'=>'FUNDING_INITIATED', 'FUNDING_DONE'=>'FUNDING_DONE', 'ACCOUNT_DETAILS_DONE'=>'ACCOUNT_DETAILS_DONE', 'VKYC_LINK_GENERATED'=>'VKYC_LINK_GENERATED'];

            return view('channelid.addtemplate')->with('activity',$activity);
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

                $activity = ['AUTH_DONE' =>'AUTH_DONE', 'PAN_DONE'=>'PAN_DONE', 'CIDD_DONE'=>'CIDD_DONE', 'NOMINATION_DONE'=>'NOMINATION_DONE', 'FUNDING_INITIATED'=>'FUNDING_INITIATED', 'FUNDING_DONE'=>'FUNDING_DONE', 'ACCOUNT_DETAILS_DONE'=>'ACCOUNT_DETAILS_DONE', 'VKYC_LINK_GENERATED'=>'VKYC_LINK_GENERATED'];

                if(isset($requestData['MESSAGE'])){

                    $requestData['MESSAGE'] = str_replace('_NL_', '<br>',$requestData['MESSAGE']);
                }

                DB::beginTransaction();
                if(isset($requestData['id'])){
                    $message = DB::table('OAO.MESSAGES')->where('ID',$requestData['id'])->update($requestData);
                }else{
                    $message = DB::table('OAO.MESSAGES')->insert($requestData);
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
            $activity = ['AUTH_DONE' =>'AUTH_DONE', 'PAN_DONE'=>'PAN_DONE', 'CIDD_DONE'=>'CIDD_DONE', 'NOMINATION_DONE'=>'NOMINATION_DONE', 'FUNDING_INITIATED'=>'FUNDING_INITIATED', 'FUNDING_DONE'=>'FUNDING_DONE', 'ACCOUNT_DETAILS_DONE'=>'ACCOUNT_DETAILS_DONE', 'VKYC_LINK_GENERATED'=>'VKYC_LINK_GENERATED'];
            $activityDetails = DB::table('OAO.MESSAGES')
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
            return view('channelid.edittemplate')
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