<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Crypt,Cache;
use JWTFactory;
use JWTAuth;
use Session;
use Cookie;
use DB;
use Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    //declare userId as global variable
    protected $userId;
    //declare roleId as global variable
    protected $roleId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //checks token exists or not
        // echo Cookie::get('token');exit;
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
        }
        $this->middleware('guest')->except('logout');
    }

    /*
     * Method Name: init
     * Created By : Sharanya T
     * Created At : 05-02-2020
   
     * Description:
     * This function is used to intialise database password
     *
     * Input Params:
     * @params $init_key
     *
     * Output:
     * Redirect to login page
    */
    public function init(Request $request)
    {
        Cache::forever('db_password', base64_encode($request['init_key']));
        return redirect()->to('/login');
    }

    /*
     * Method Name: init
     * Created By : Sharanya T
     * Created At : 05-02-2020
   
     * Description:
     * This function is used to authenticate to active directory
     *
     * Input Params:
     * @params $username,$password
     *
     * Output:
     * Redirect to landing page based on role
    */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password','shash');
            $username = $credentials['username'];
            $key = substr($credentials['password'], strlen($credentials['password'])-16,6);
            $password = substr($credentials['password'],0,-17);
            $password = CommonFunctions::decrypt256($password,$key);
            if(env('APP_SETUP') == 'DEV'){
                if($password == "cube@1234")
                {

                }else{
                    return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                }
            }
            //assign username from request
            $username = strtolower($request['username']);
            //assign password from request
            $auth_check = 0;
            //make true for active directory authentication
            if(env('APP_SETUP') == 'DEV'){
                $is_ldap_auth = false;
            }else{
                $is_ldap_auth = true;
            }
            $testing = false;
            //checks loggged in user is admin or not


            if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){
                if(($username == "admin") && ($password == base64_decode(env('ADMIN_PWD'))))
                {
                    $auth_check = 1;
                }else{
                    if ($username == "admin") {
                        $comments = "Failed authentication attempt for user ".$username.".";
                        $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                        return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    } 
                }
            }

            //checks ldap authentication
            if(($is_ldap_auth) && (!$auth_check))
            {
                $ldap_server = env('LDAP_HOSTS');
                $ldap_conn = ldap_connect($ldap_server)  or die("Failed to connect to LDAP server.");
                if(!ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3)){
                    echo "Could not set LDAPv3\r\n"; exit;
                }else{
                    $user_format = env('LDAP_USER_FORMAT', 'cn=%s,'.env('LDAP_BASE_DN', ''));
                    //$userdn = sprintf($user_format, $username);
                    if($username == '' || $password == ''){
                        return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    }
                    
                    $userdn = $username.'@dcbindia.com';
                    $result = @ldap_bind($ldap_conn, $userdn, $password);
                    
                    if($result)
                    {

                    }else{
                        $comments = "Failed authentication attempt for user ".$username.".";
                        $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                        return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    }
                }
            }
            
            $userDetails = DB::table('USERS')->where(DB::raw('LOWER(EMPLDAPUSERID)'),strtolower($username))
                                                ->where('EMPSTATUS','Y')
                                                ->get()->toArray();
                                                

            if(count($userDetails) > 0)
            {
                $userDetails = (array) current($userDetails);
                if(Cache::get($userDetails['id'].'_is_logged_in') && $testing)
                {
                    $comments = "Concurrent login attempt for user ".$username.".";
                    $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                    return redirect()->back()->withErrors(['User '.$username.' already logged in. Concurrent login not permitted!']);
                }else{
                    Cache::put($userDetails['id'].'_is_logged_in', 1,900);
                }
                //define varaible to create token
                $factory = JWTFactory::customClaims([
                    'sub'   => $userDetails['hrmsno'] ?? '',
                    'user_id' => $userDetails['id'],
                    'role_id' => $userDetails['role'],
                ]);
         
                $checkinspect = DB::table('APPLICATION_SETTINGS')->select('FIELD_VALUE','FIELD_NAME')->whereIn('FIELD_NAME',['_ALLOW_THIS_USER_DEBUG',"MASKING_TIME","IS_ALLOW_HUF"])->get()->toArray();

                foreach ($checkinspect as $key => $value) {
                  
                    if($value->field_name == "MASKING_TIME"){
                        Session::put("mask_timer",$value->field_value);
                    }elseif($value->field_name == "_ALLOW_THIS_USER_DEBUG"){
                        Cache::put('_ALLOW_THIS_USER_DEBUG',$value->field_value);
                	
                    }elseif($value->field_name == "IS_ALLOW_HUF"){
                        Session::put("is_allow_huf",$value->field_value);
                    }
                }

                $payload = $factory->make();
                $token = JWTAuth::encode($payload);
                Cookie::queue('token', $token);
                Session::put('userId', $userDetails['id']);
                Session::put('branchId',$userDetails['empsol']);
                Session::put('username', $username);
                Session::put('role', $userDetails['role']);
                Session::put('normal_flag',$userDetails['normal_flag']);
                Session::put('nr_flag',$userDetails['nr_flag']);
                Session::put('priority_flag',$userDetails['priority_flag']);
                    // echo '<pre>'; print_r($userDetails['priority_flag']); exit;
               
                Session::put('lastLogin', self::getlastloggin($userDetails['id']));
                
                $clientFinger = md5($request->ip().$request->server('HTTP_USER_AGENT'));
                Session::put('clientFinger', $clientFinger);

                self::updateLastLoginData($userDetails['id']);
              
                $createUserLog = CommonFunctions::createUserLog($request,$userDetails['id']);
                return redirect()->to(config('constants.DASHBOARD_URLS.'.$userDetails['role']));
            }else{
                $comments = "Failed authentication attempt for user ".$username.".";
                $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                return redirect()->back()->withErrors(['Authentication failure. Please check if ID is enabled and retry.']);

            }
        } catch( \Illuminate\Database\QueryException $e)  {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
     * Method Name: logout
     * Created By : Sharanya T
     * Created At : 05-02-2020
   
     * Description:
     * This function is used to logout the page along removing cookie's
     *
     * Input Params:
     * @params $userId,$token
     *
     * Output:
     * Redirect to login page
    */
    public function logout(Request $request){
        $tokenCheck = JWTAuth::getToken();
        if($tokenCheck == ''){
            return redirect()->to('/login');
        }
        JWTAuth::invalidate(JWTAuth::getToken());
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cache::forget( $this->userId.'_is_logged_in' );
        $currentToken = hash('sha256', Cookie::get('token'));
        Session::put('userId','');
        Cache::put($currentToken,'is_expired',7200);      
        return redirect()->to('/login')->withCookie(Cookie::forget('token'));
    }

    public function updateLastLoginData($userId){
        try{

            $currDate = Carbon::now();
            $updateUserData = DB::table('USERS')->whereId($userId)->update(['LAST_LOGIN'=>$currDate]);

        }catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public function getlastloggin($userId)
    {
        try{    
               

                $getUserDatils = DB::table('USERS')->select('LAST_LOGIN')->whereId($userId)->get()->toArray();
                
                // $getUserDatils = DB::table('USER_ACTIVITY_LOG')->where(['USER_ID'=>$userId,'ACTION'=>'login'])
                //                                                 ->orderBy('CREATED_AT','DESC')
                //                                                 ->limit(1)
                //                                                 ->get()->toArray();
                
                
                if(count($getUserDatils)>0){
                        $getUserDatils = (array) current($getUserDatils);
                        
                        if($getUserDatils['last_login'] != ''){
                            $loggedin_time = Carbon::parse($getUserDatils['last_login'])->format('M d Y, g:i A');
                        }else{
                            $loggedin_time = '';
                        }
                    return $loggedin_time;
                }else{
                    return '';
                }
        }catch(\Illuminate\Database\QueryException $e) {
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }
}
