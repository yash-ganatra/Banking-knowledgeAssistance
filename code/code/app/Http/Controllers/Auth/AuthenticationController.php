<?php

namespace App\Http\Controllers\Auth;;

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

class AuthenticationController extends Controller
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
    // protected $redirectTo = '/home';
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
        //$this->middleware('guest')->except('logout');
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


     public static function authenticate($password)
    {
        try {
             
            $username = Session::get('username');
            
            if(($username == '') || ($password == '')){
        	   return false;
            }


            $key = substr($password, strlen($password)-16,6);
            //assign password
            $password = substr($password,0,-17);
            // echo $password;exit;
            $password = CommonFunctions::decrypt256($password,$key);


            $ldap_server = env('LDAP_HOSTS');

            $ldap_conn = ldap_connect($ldap_server)  or die("Failed to connect to LDAP server.");

            if(!ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3)){
                return false;
            }
        
            $userdn = strtolower($username.'@dcbindia.com');
            //echo "<pre>";print_r($userdn);exit;
            
            $result = @ldap_bind($ldap_conn, $userdn, $password);

            return $result;
                
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Auth/AuthenticationController','authenticate',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }




}

?>
