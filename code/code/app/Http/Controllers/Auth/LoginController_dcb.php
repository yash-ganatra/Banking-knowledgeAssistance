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
            //assign username
            $username = $credentials['username'];
            $key = substr($credentials['password'], strlen($credentials['password'])-16,6);
            //assign password
            $password = substr($credentials['password'],0,-17);
            // echo $password;exit;
            $password = CommonFunctions::decrypt256($password,$key);
            // if($password == "cube@1234")
            // {

            // }else{
            //     return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
            // }
            //assign username from request
            $username = strtolower($request['username']);
            //assign password from request
            //$password = $request['password'];
            $auth_check = 0;
            //make true for active directory authentication
            $is_ldap_auth = true;
            $testing = false;
            //checks loggged in user is admin or not
            if(($username == "admin") && ($password == base64_decode(env('ADMIN_PWD'))))
            {   

                $auth_check = 1;
            }else{
                if ($username == "admin") {
                    //failed to authenticate
                    //assign comments for log
                    $comments = "Failed authentication attempt for user ".$username.".";
                    //create log for failed to autheticate details
                    $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                    //redirect to login page with error message
                    return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
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
                    if($username != ''){

                       $userdn = $username.'@dcbindia.com';
                    }else{
                        return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    }

                    //echo "<pre>";print_r($userdn);
                    //echo "<pre>";print_r($password);exit;
                    

                    if(($userdn != '') && ($password != '')){
  
                        $result = @ldap_bind($ldap_conn, $userdn, $password);
                        
                    }else{
                        return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    }
                    
                    if($result)
                    {

                    }else{
                        //assign comments for log
                        $comments = "Failed authentication attempt for user ".$username.".";
                        //create log for failed to autheticate details
                        $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                        //redirect to login page with error message
                        
                            return redirect()->back()->withErrors(['Please Enter Valid Credentials']);
                    
                    }
                }
            }
            //fetch user details
            $userDetails = DB::table('USERS')->where('EMPLDAPUSERID',$username)->get()->toArray();
            //check user details exists in our datatabse or not
            if(count($userDetails) > 0)
            {
                //assign user details
                $userDetails = (array) current($userDetails);
                //checks user is alredy logged in any another browser/system
                if(Cache::get($userDetails['id'].'_is_logged_in') && $testing)
                // if(Cache::get($userDetails['id'].'_is_logged_in'))
                {
                    //assign comments for log
                    $comments = "Concurrent login attempt for user ".$username.".";
                    //create log for failed to autheticate details
                    $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                    //redirect to login page with error message
                    return redirect()->back()->withErrors(['User '.$username.' already logged in. Concurrent login not permitted']);
                }else{
                    //adding logged in user id into cache which indicates user is logged in
                    Cache::put($userDetails['id'].'_is_logged_in', 1,900);
                }
                //define varaible to create token
                $factory = JWTFactory::customClaims([
                    'sub'   => $userDetails['hrmsno'],
                    'user_id' => $userDetails['id'],
                    'role_id' => $userDetails['role'],
                ]);
                $payload = $factory->make();
                //create token
                $token = JWTAuth::encode($payload);
                //store token in cookie
                Cookie::queue('token', $token);
                //store user id in session
                Session::put('userId', $userDetails['id']);
                //store user name in session
                Session::put('branchId',$userDetails['empsol']);
                //store user name in session
                Session::put('username', $username);
                Session::put('role', $userDetails['role']);
                // echo Session::get('role');exit;
                //create log for login details
                $createUserLog = CommonFunctions::createUserLog($request,$userDetails['id']);
                //reddirect to dashboard page based on role
                return redirect()->to(config('constants.DASHBOARD_URLS.'.$userDetails['role']));
                // return redirect()->to('/bank/dashboard');
                // return redirect()->to('/bank/addaccount');
                // return redirect()->to('/index');
            }else{
                //assign comments for log
                $comments = "Failed authentication attempt for user ".$username.".";
                //create log for failed to autheticate details
                $createUserLog = CommonFunctions::createUserLog($request,'',true,$comments);
                //redirect to login page with error message
                return redirect()->back()->withErrors(['Authentication failure. Please check if ID is enabled and retry.']);
            }
        } catch( \Illuminate\Database\QueryException $e)  {
            dd($e->getMessage());
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
    public function logout(){
        // $cookie = Cookie::queue(Cookie::forget('token'));
        // \Cookie::forget('token');
        // $cookie = \Cookie::forget('laravel_session');
        // Cookie::queue('laravel_session', 'test');
        // \Cookie::queue(\Cookie::forget('token'));
        // \Cookie::forget('laravel_session');
        // echo $cookie;
        // echo Cookie::get('token');
        // exit;
        // Cookie::forget('token');
        Session::flush();
        //remove cache for user loggedin condition
        Cache::forget( $this->userId.'_is_logged_in' );
        $currentToken = hash('sha256', Cookie::get('token'));
        // $currentToken = Cookie::get('token');
        // echo "<pre>";print_r($currentToken);exit;
        Cache::put($currentToken,'is_expired',7200);
        // Cache::put('is_expired',1,7200);
        //redirect to login page
        return redirect()->to('/login')->withCookie(Cookie::forget('token'));
        // return redirect()->to('/login');
    }
}
