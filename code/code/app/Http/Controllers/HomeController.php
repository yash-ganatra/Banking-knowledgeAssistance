<?php

namespace App\Http\Controllers;

use App\Helpers\CommonFunctions;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use SoulDoit\DataTable\SSP;
use Session;
use Cookie;
use Crypt;
use Cache;
use DB;

class HomeController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/HomeController','userapplications','Unauthorized attempt detected by '.$this->userId,'','','1');

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

    public function dashboard()
    {
        echo "Welcome";exit;
    }

    public function userapplications(Request $request)
    {
        try {
            $requestData = $request->get('data');
            $select_arr = [];
            $filteredColumns = ['ID','USER_NAME','APPLICATION_ID','SENT_ON','STATUS'];
            $i=0;
            foreach ($filteredColumns as $column) {
                if($column == "USER_NAME")
                {
                    $user_name = "USER_FIRST_NAME || ' ' || USER_MIDDLE_NAME || ' ' || USER_LAST_NAME AS user_name";
                    array_push($select_arr, DB::raw($user_name));
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            return $html;
                        }
                    );
                }/*else if($column == "SENT_ON"){
                    $dt[$i] = array( 'db' => DB::raw($user_name),'dt' => $i,
                        'formatter' => function( $d, $row ) {
                            $html = $row->user_name;
                            return $html;
                        }
                    );
                }*/else{
                    array_push($select_arr, $column);
                    $dt[$i]['label'] = $column;
                    $dt[$i]['db'] = strtolower($column);
                    $dt[$i]['dt'] = $i;    
                }                
                $i++;              
            }
            // $dt_obj = (new SSP('USER_APPLICATIONS', $dt))->order(0, 'desc');
            $dt_ssp_obj = new SSP();
            $dt_ssp_obj->setColumns($dt)->orderBy(0, 'desc');
            $dt_obj = DB::table('USER_APPLICATIONS')->select($select_arr);

            $dt_ssp_obj->setQuery($dt_obj);
            $dd = $dt_ssp_obj->getData();
            // $dd['items'] = (array) $dd['items'];
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


    
       /*
    * Method Name: getchatusers
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to get users with message count
    *
    * Input Params:
    * @$searchParam,@userId
    *
    * Output:
    * Returns view template
    */
    public function getchatusers(Request $request)
    {
        try {
            //checks request is ajax call or not
            if ($request->ajax()){
                //assign data from request
                $requestData = $request->get('data');
                //echo "$requestData";exit;
                //Array to hold users
                $users = array();
                //array to hold temporary array
                $temp = array();
                //Array to hold user details
                $userDetails = array();
                //variable to store search param
                $searchParam = '';
                //cehck search parameter is exists or not
                if(isset($requestData['searchParam'])){
                    //assigns search param value
                    $searchParam = $requestData['searchParam'];

                    /*if(CommonFunctions::isDangerous($searchParam,'INPUT')){
                         return json_encode(['status'=>'fail','msg'=>'Error! Invalid Input','data'=>[]]);
                    }
                    else{
                        $searchParam = CommonFunctions::inputRegexValidation($searchParam,'INPUT');
                    }*/
                }
                //fetch users list
                $getUsers = DB::table('USERS')->select('USERS.ID','EMPMOBILENO','USER_ROLES.ROLE as ROLE',
                                                        DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS name"))
                                                ->leftjoin('USER_ROLES','USER_ROLES.ID','USERS.ROLE')
                                                ->where('USERS.ID','!=',$this->userId);
                if($searchParam != '')
                {
                    $getUsers = $getUsers->whereRaw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME LIKE '%".$searchParam."%'");
                }
                $getUsers = $getUsers->get()->toArray();
                
                //fetch mesaage count group by user
                $usersDetailsWithChatCount = DB::table('CHAT')->select('SENDER_ID',DB::raw('count(*) as chatcount'))
                                                        ->where(['RECEIPENT_ID'=>$this->userId,'IS_READ'=>0])
                                                        ->groupBy('SENDER_ID')
                                                        ->pluck('chatcount','sender_id')->toArray();

                if(count($getUsers) > 0)
                {
                    foreach ($getUsers as $user) {
                        $user = (array) $user;
                        $users[$user['id']] = $user;
                        //checks message count exists for that user or not
                        if(in_array($user['id'], array_keys($usersDetailsWithChatCount)))
                        {
                            //assign user count for user
                            $user['msgcount'] = $usersDetailsWithChatCount[$user['id']];
                            //pushing user details to temp array
                            array_push($temp, $user);
                            //removing user from users details
                            unset($users[$user['id']]);
                        }
                    }
                }
                //merge temp and user array
                $userDetails = array_merge($temp,$users);
                //updating cache value to 0 after viewing chat
                Cache::forever($this->userId.'_has_message',0);
                return view('userslist')->with('users',$userDetails);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    * Method Name: getuserchatbyid
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to get messages of loggedin user and selected user
    *
    * Input Params:
    * @$receipentId,@$senderId
    *
    * Output:
    * Returns view template
    */
    public function getuserchatbyid(Request $request)
    {
        try {
            //checks request is ajax call or not
            if ($request->ajax()){
                //assign data from request
                $requestData = $request->get('data');
                //assign loggedin user as sender id
                $senderId = $this->userId;
                //assign selected user as recipent id
                $receipentId = $requestData['userId'];
                //fetch messages based on sender id and receipent id
                $chatDetails = DB::table('CHAT')->select('CHAT_ID','SENDER_ID','RECEIPENT_ID','CHAT_TEXT','CHAT.CREATED_AT','CHAT.IS_READ',
                                            DB::raw("EMP_FIRST_NAME || ' ' || EMP_MIDDLE_NAME || ' ' || EMP_LAST_NAME as sender_name"))
                                        ->leftjoin('USERS','USERS.ID','CHAT.SENDER_ID')
                                        ->where(function ($query) use ($senderId,$receipentId)  {
                                            $query->where(['SENDER_ID'=>$senderId,'RECEIPENT_ID'=>$receipentId]);
                                        })
                                        ->orWhere(function ($query) use ($senderId,$receipentId)  {
                                            $query->where(['SENDER_ID'=>$receipentId,'RECEIPENT_ID'=>$senderId]);
                                        })
                                        ->orderBy('CHAT.CREATED_AT')
                                        ->limit(config('constants.DEFAULT.MESSAGES_COUNT'))
                                        ->get()->toArray();
                //fetch selected user(receipent) details
                $receipent = DB::table('USERS')->select('ID',DB::raw("EMP_FIRST_NAME || ' ' || EMP_MIDDLE_NAME || ' ' || 
                                                                            EMP_LAST_NAME as receipent_name "))
                                                ->where('ID',$receipentId)
                                                ->pluck('receipent_name','id')->toArray();
                //render template
                return view('userchat')->with('chatDetails',$chatDetails)->with('senderId',$senderId)
                                        ->with('receipent',$receipent);
            }            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }



    public function getuserlastchatid(Request $request)
    {
        try {
            //checks request is ajax call or not
            if ($request->ajax()){
                //assign data from request
                $requestData = $request->get('data');
                //assign loggedin user as sender id
                $senderId = $this->userId;
                //assign selected user as recipent id
                $receipentId = $requestData['userId'];
                //fetch messages based on sender id and receipent id
                $chatDetails = DB::table('CHAT')->select('CHAT_ID','SENDER_ID','RECEIPENT_ID','CHAT_TEXT','CHAT.CREATED_AT','CHAT.IS_READ',
                                            DB::raw("EMP_FIRST_NAME || ' ' || EMP_MIDDLE_NAME || ' ' || EMP_LAST_NAME as sender_name"))
                                        ->leftjoin('USERS','USERS.ID','CHAT.SENDER_ID')
                                        ->where(function ($query) use ($senderId,$receipentId)  {
                                            $query->where(['SENDER_ID'=>$senderId,'RECEIPENT_ID'=>$receipentId]);
                                        })
                                        ->orWhere(function ($query) use ($senderId,$receipentId)  {
                                            $query->where(['SENDER_ID'=>$receipentId,'RECEIPENT_ID'=>$senderId]);
                                        })
                                        ->orderBy('CHAT.CREATED_AT')
                                        ->limit(config('constants.DEFAULT.MESSAGES_COUNT'))
                                        ->get()->toArray();

                $lastChatId = $chatDetails[count($chatDetails)-1];
                //fetch selected user(receipent) details

               // echo "<pre>";print_r($receipent);exit;
                //render template
                return json_encode($lastChatId);
            }            
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    /*
    * Method Name: showlastloggin
    * Created By : Sharanya T
    *
    * Description:
    * This function is used save messages from chat
    *
    * Input Params:
    * @$userId
    *
    * Output:
    * Returns Json
    */
    public function savemessage(Request $request)
    {
        try{
            //checks request is ajax call or not
            if ($request->ajax()){
                //assign data from request except call back function name
                $insertData = Arr::except($request->get('data'),'functionName');
             
                    if(isset($insertData['chat_text'])){
                    //assign search param value
                    $searchParam = $insertData['chat_text'];
                    if(CommonFunctions::isDangerous($searchParam,'COMMENTS')){
                         return json_encode(['status'=>'fail','msg'=>'Error! Invalid Input','data'=>[]]);
                    }
                    else{
                        $searchParam = CommonFunctions::inputRegexValidation($searchParam,'COMMENTS');
                    }
                }
                //assign sender id as loggedin user id
                $insertData['sender_id'] = $this->userId;
                //insert message
                $message = DB::table('CHAT')->insert($insertData);
                if($message){
                    //store cache that user(receipent) got a message
                    Cache::forever($insertData['receipent_id'].'_has_message',1);
                    return json_encode(['status'=>'success','msg'=>'Message sent successfully.','data'=>$insertData]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    * Method Name: updateisread
    * Created By : Sharanya T
    *
    * Description:
    * This function is used update is_read
    *
    * Input Params:
    * @$userId
    *
    * Output:
    * Returns Json
    */
    public function updateisread(Request $request)
    {
        try{
            //checks request is ajax call or not
            if ($request->ajax()){
                //assign data from request
                $requestData = $request->get('data');
                //update is_read by user_id
                $updateisread = DB::table('CHAT')->where('SENDER_ID',$requestData['userId'])->update(['IS_READ'=>1]);
                //checks is_read is updated or not
                if($updateisread)
                {
                    return json_encode(['status'=>'success','msg'=>'updated successfully.','userId'=>$requestData['userId']]);
                }else{
                    return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function userhelp()
    {
        $user_helps = DB::table('USER_HELP')
                        ->where('ACTIVE','Y')
                        ->get()->toArray();
        $thumbs = ['imgThumb'=> "public/images/auditor-work-flow.jpg",
                    'videoThumb'=> "public/images/login.png"];
        
        return view('userhelp')->with('items',$user_helps)
                                ->with('thumbs',$thumbs);
    }

}
?>