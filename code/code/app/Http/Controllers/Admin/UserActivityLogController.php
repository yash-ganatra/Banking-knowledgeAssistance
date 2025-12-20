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

class UserActivityLogController extends Controller
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
                $saveuserlog = CommonFunctions::createUserLogDirect('Admin/UserActivityLogController','useractivitylog','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }
        }
    }

    /*
    * Method Name: useractivitylog
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to display users activities log
    *
    * Input Params:
    * @$users
    *
    * Output:
    * Returns view template
    */
    public function useractivitylog(Request $request)
    {
        try {
            //fetch all users
            $users = CommonFunctions::getusers();
            $log_refresh_timers = config('constants.LOG_REFRESH_TIMER');
            // echo "<pre>";print_r($log_refresh_timers);exit;
            //render mapping template
            //->with('role',config('constants.ROLES_IDS')[$this->roleId])
            return view('admin.useractivitylog')->with('modules',config('constants.MODULES'))
                                                ->with('users',$users)
                                                ->with('log_refresh_timers',$log_refresh_timers)
                                                ;
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
    * Method Name: activitylogs
    * Created By : Sharanya T
    *
    * Description:
    * This function is used to get users activities log
    *
    * Input Params:
    * @$user,@$module,@$startDate,@$endDate
    *
    * Output:
    * Returns Json.
    */
    public function activitylogs(Request $request)
    {
        try{
            if ($request->ajax()){
                $requestData =  $request->get('data');
                 //echo "<pre>";print_r($filteredColumns);exit;
                //table coulmns to display
                $array_column = [];
                $filteredColumns = ['USER_ACTIVITY_LOG.ID','USER_ACTIVITY_LOG.CREATED_AT','USER_ACTIVITY_LOG.USER_ID','USER_ACTIVITY_LOG.URL','USER_ACTIVITY_LOG.MODULE','USER_ACTIVITY_LOG.CONTROLLER','USER_ACTIVITY_LOG.ACTION','USER_ACTIVITY_LOG.IP_ADDRESS','USER_ACTIVITY_LOG.COMMENTS'];


                $i=0;
                //build dt array based on columns
                foreach ($filteredColumns as $column) {
                    if($column == "USER_ACTIVITY_LOG.CREATED_AT"){
                        array_push($array_column, strtolower('USER_ACTIVITY_LOG.CREATED_AT'));
                        $dt[$i] = array( 'db' => strtolower('USER_ACTIVITY_LOG.CREATED_AT'),'dt' => $i,
                            'formatter' => function( $d, $row ) {
                                $html = Carbon::parse($row->created_at)->format('M d,g:i A');
                                return $html;
                            }
                        );
                    }else{
                        if($column == "USER_ID"){
                            $user_name = "USERS.EMP_FIRST_NAME||' '||USERS.EMP_MIDDLE_NAME||' '||USERS.EMP_LAST_NAME AS emp_name";
                            // array_push($array_column,DB::raw($user_name));
                            //$column = DB::raw($user_name);
                        }
                        array_push($array_column,$column);
                        $dt[$i]['label'] = $column;
                        $dt[$i]['db'] = strtolower($column);
                        $dt[$i]['dt'] = $i;
                    }
                    $i++;
                }
                // $dt_obj = (new SSP('USER_ACTIVITY_LOG', $dt));
                $dt_ssp_obj = new SSP();
                $dt_ssp_obj->setColumns($dt);

                $dt_obj = DB::table('USER_ACTIVITY_LOG')->select($array_column);

                $dt_obj = $dt_obj->leftJoin('USERS','USERS.ID','USER_ACTIVITY_LOG.USER_ID');
                $dt_obj = $dt_obj->where("USER_ACTIVITY_LOG.CREATED_AT",'>=',Carbon::now()->subDays(1));
                
                //checks userid is empty or not
                if(isset($requestData['user'])){
                    $dt_obj = $dt_obj->where('USER_ACTIVITY_LOG.USER_ID',$requestData['user']);
                }
                //checks module is empty or not
                // echo "<pre>";print_r(strlen($requestData['module']));exit;
            if(isset($requestData['module']) && ($requestData['module'] != '')){
                $dt_obj = $dt_obj->whereRaw(DB::raw("upper(USER_ACTIVITY_LOG.MODULE) like '%' || ? || '%' "), strtoupper($requestData['module']));
            }
                

                //checks sent date is empty or not
            if($requestData['startDate'] != '')
            {
                $dt_obj = $dt_obj->whereRaw("to_char(USER_ACTIVITY_LOG.CREATED_AT,'DD-MM-YYYY')>='".$requestData['startDate']."'")
                                ->whereRaw("to_char(USER_ACTIVITY_LOG.CREATED_AT,'DD-MM-YYYY')<='".$requestData['endDate']."'");
            }

           

                 //checks user name is empty or not
            if($requestData['users'] != '')
            {
                $dt_obj = $dt_obj->where('USERS.ID',$requestData['users']);
            }

                $dt_obj = $dt_obj->orderBy('ID', 'DESC');
                $dt_ssp_obj->setQuery($dt_obj);
                $dd = $dt_ssp_obj->getData();

                $dd["items"] = (array) $dd["items"];
                $dd["items"] = array_map(fn($items)=> array_values( (array) $items) ,$dd["items"]);
           
                return response()->json(["draw"=>1,"recordsTotal"=>"$dd[total_item_count]","recordsFiltered"=>"$dd[total_filtered_item_count]","data"=>$dd["items"]]);
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