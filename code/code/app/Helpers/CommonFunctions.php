<?php
namespace App\Helpers;

use Illuminate\Support\Arr;
use Intervention\Image\Facades\Image;
use Sop\JWX\JWE\JWE;
use Carbon\Carbon;
use Session;
use Route;
use Cache;
use File;
use DB;
use PDF;
use App\Http\Controllers\Admin\ExceptionController;


class CommonFunctions{

    /*
     * Method Name: createUserLog
     * Created By : Sharanya T
     * Created At : 05-02-2020

     * Description:
     * This function is used to save activity log
     *
     * Input Params:
     * @params $hrmsNo,$is_failed,$comments
     *
     * Output:
     * Returns Success/Error.
    */

public static function createUserLog($request,$hrmsNo,$is_failed=false,$comments='')
    {
        //get the route
        $route = Route::getRoutes()->match($request);
        $userLog['user_id'] = $hrmsNo;
        $userLog['url'] = $request->url();
        //get action name
        list($controller, $userLog['action']) = explode('@', $route->getActionName());
        //get module name
        $module = explode('\Controllers\\', $controller);
        $module = explode('\\', $module[1])[0];
        //get controller name
        $controller = preg_replace('/.*\\\/', '', $controller);

        if($module != $controller)
        {
            $module = $module;
        }else{
            if($route->getName() == "userhelp"){
                $module = "UserInfo";
            }elseif($route->getName() == "userchat"){
                $module = "Chat";
            }else{
                $module = "Email";
            }
        }
        $userLog['module'] = $module;
        $userLog['controller'] = $controller;
        $userLog['ip_address'] = $request->ip();
        $userLog['created_by'] = $hrmsNo;
        $userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $module.$controller.$request->ip().$hrmsNo.$userLog['integrity_time'].$userLog['action'];
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);
        if($is_failed)
        {
            $userLog['comments'] = $comments;
        }
        $createUserLog = DB::table('USER_ACTIVITY_LOG')->insert($userLog);
        return $createUserLog;
    }

    public static function createUserLogDirect($controller, $function, $comments, $old_value='', $new_value='',$created_by=1)
    {
        $userLog['user_id'] = Session::get('userId');
        $userLog['controller'] = $controller;
		$userLog['action'] = $function;
		$userLog['old_value'] = $old_value;
		$userLog['new_value'] = $new_value;
        $userLog['created_by'] = $created_by;

		$userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $controller.$userLog['integrity_time'].$function;
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);

		$userLog['comments'] = 'FORM '.Session::get('formId').' '.$comments;
        $createUserLog = DB::table('USER_ACTIVITY_LOG')->insert($userLog);
        DB::commit();
		return $createUserLog;
    }

    public static function createImageDeleteLog($request,$hrmsNo,$is_failed=false,$comments='', $old_value='')
    {
        //get the route
        $route = Route::getRoutes()->match($request);
        $userLog['user_id'] = $hrmsNo;
        $userLog['url'] = $request->url();
        //get action name
        list($controller, $userLog['action']) = explode('@', $route->getActionName());
        //get module name
        $module = explode('\Controllers\\', $controller);
        $module = explode('\\', $module[1])[0];
        //get controller name
        $controller = preg_replace('/.*\\\/', '', $controller);

        if($module != $controller)
        {
            $module = $module;
        }else{
            if($route->getName() == "userhelp"){
                $module = "UserInfo";
            }elseif($route->getName() == "userchat"){
                $module = "Chat";
            }else{
                $module = "Email";
            }
        }
        $userLog['module'] = $module;
        $userLog['controller'] = $controller;
        $userLog['ip_address'] = $request->ip();
        $userLog['created_by'] = $hrmsNo;
        $userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $module.$controller.$request->ip().$hrmsNo.$userLog['integrity_time'].$userLog['action'];
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);
        if($is_failed)
        {
            $userLog['comments'] = $comments;
        }
        $userLog['old_value'] = $old_value;
        // echo "<pre>";print_r($is_failed);exit;
        // echo "<pre>";print_r($userLog);exit;
        $createUserLog = DB::table('USER_ACTIVITY_LOG')->insert($userLog);
        return $createUserLog;
    }

    public static function createAddEditUserLog($request,$hrmsNo,$is_failed=false,$comments='', $old_value='', $new_value='')
    {
        //get the route
        $route = Route::getRoutes()->match($request);
        $userLog['user_id'] = $hrmsNo;
        $userLog['url'] = $request->url();
        //get action name
        list($controller, $userLog['action']) = explode('@', $route->getActionName());
        //get module name
        $module = explode('\Controllers\\', $controller);
        $module = explode('\\', $module[1])[0];
        //get controller name
        $controller = preg_replace('/.*\\\/', '', $controller);

        if($module != $controller)
        {
            $module = $module;
        }else{
            if($route->getName() == "userhelp"){
                $module = "UserInfo";
            }elseif($route->getName() == "userchat"){
                $module = "Chat";
            }else{
                $module = "Email";
            }
        }
        $userLog['module'] = $module;
        $userLog['controller'] = $controller;
        $userLog['ip_address'] = $request->ip();
        $userLog['created_by'] = $hrmsNo;
        $userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $module.$controller.$request->ip().$hrmsNo.$userLog['integrity_time'].$userLog['action'];
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);
        // echo "<pre>";print_r($comments);
        // echo "<pre>";print_r($old_value);
        // echo "<pre>";print_r('-');
        // echo "<pre>";print_r($new_value);exit;
        if($is_failed)
        {
            $userLog['comments'] = $comments;
            $userLog['old_value'] = $old_value;
            $userLog['new_value'] = $new_value;
        }else{
            $userLog['comments'] = 'failed';
        }
        //$userLog['old_value'] = $old_value;
        // echo "<pre>";print_r($is_failed);exit;
        // echo "<pre>";print_r($userLog);exit;
        $createUserLog = DB::table('USER_ACTIVITY_LOG')->insert($userLog);
        return $createUserLog;
    }


    public static function uamcreateAddEditUserLog($request,$hrmsNo,$is_failed=false,$comments='', $old_value='', $new_value='')
    {
        //get the route
        $route = Route::getRoutes()->match($request);
        $userLog['user_id'] = $hrmsNo;
        $userLog['url'] = $request->url();
        //get action name
        list($controller, $userLog['action']) = explode('@', $route->getActionName());
        //get module name
        $module = explode('\Controllers\\', $controller);
        $module = explode('\\', $module[1])[0];
        //get controller name
        $controller = preg_replace('/.*\\\/', '', $controller);

        if($module != $controller)
        {
            $module = $module;
        }else{
            if($route->getName() == "userhelp"){
                $module = "UserInfo";
            }elseif($route->getName() == "userchat"){
                $module = "Chat";
            }else{
                $module = "Email";
            }
        }
        $userLog['module'] = $module;
        $userLog['controller'] = $controller;
        $userLog['ip_address'] = $request->ip();
        $userLog['created_by'] = $hrmsNo;
        $userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $module.$controller.$request->ip().$hrmsNo.$userLog['integrity_time'].$userLog['action'];
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);
    
        if($is_failed)
        {
            $userLog['comments'] = $comments;
            $userLog['old_value'] = $old_value;
            $userLog['new_value'] = $new_value;
        }else{
            $userLog['comments'] = 'failed';
        }
        
        $createUserLog = DB::table('UAM_USER_ACTIVITY_LOG')->insert($userLog);
        return $createUserLog;
    }

    /*
    *  Method Name: getapplicationSettingsDetails
    *  Created By : Sharanya T
    *  Created At : 10-02-2020
    *
    *  Description:
    *  Method to fetch applcaition settings from database
    *
    *  Params:
    *  @$variable
    *
    *  Output:
    *  Returns String.
    */
    public static function getapplicationSettingsDetails($variable)
    {
        //Variable to store field value
        $field_value = '';
        //get applcaiton settings by field name
        $settingsDetails = DB::table('APPLICATION_SETTINGS')->where('FIELD_NAME',strtoupper($variable))
                                            ->pluck('field_value','secure')->toArray();
        //checks setting details empty or not
        if(count($settingsDetails) > 0)
        {
            //assign field value
            $field_value = $settingsDetails[key($settingsDetails)];
            //checks filed value is secure or not
            if(key($settingsDetails))
            {
                //decrypt secure value
                $field_value = self::decrypt256($field_value,self::getrandomIV());
            }
        }
        //returns field value
        return $field_value;
    }

    public static function getrandomIV()
    {
        return "417070496e536f75726365";
    }

    public static function encryptMethodLength()
    {
        $encryptMethod = 'AES-256-CBC';
        $number = filter_var($encryptMethod, FILTER_SANITIZE_NUMBER_INT);

        // return intval(abs($number));
        return abs(intval($number));
    }

    /*
     * Method Name: decrypt256
     * Created By : Sharanya T

     * Description:
     * This function is used to get Courier Company List
     *
     * Input Params:
     * @params $encryptedString, $key
     *
     * Output:
     * Returns decrypted data(String)
     */
    public static function getcourier()
    {
        $courier = DB::table('COURIER_LIST')->select('COURIER_ID AS id','COURIER_COMPANY AS courier_company')
                                    ->pluck('courier_company','id')->toArray();

        return $courier;
    }

    public static function getStates()
    {
        $states = array();

        $states = DB::table('BRANCH')
                                    ->pluck('state','state_id')->toArray();

        return $states;
    }


    public static function getCityByStateId($stateId)
    {
        //echo "sdfd";exit;
        $cities = DB::table('BRANCH')->where('STATE_ID',$stateId)->pluck('city','city_id')->toArray();
        return $cities;
    }

    public static function getBranch()
    {

        $branch = DB::table('BRANCH')->select('branch_name','branch_id')->pluck('branch_name','branch_id')->toArray();

        foreach ($branch as $key => $value) {
               $branch[$key] = $value.' - '.$key;
        }

        return $branch;
    }


    /*
     * Method Name: encrypt256
     * Created By : Sharanya T

     * Description:
     * This function is used to encrypt data
     *
     * Input Params:
     * @params $string, $key
     *
     * Output:
     * Returns encrypted data
     */
    public static function encrypt256($string = '', $key = "")
    {
        try {
            $encryptMethod = 'AES-256-CBC';
            $ivLength = openssl_cipher_iv_length($encryptMethod);
            $iv = openssl_random_pseudo_bytes($ivLength);

            $salt = openssl_random_pseudo_bytes(256);
            $iterations = 999;
            $hashKey = hash_pbkdf2('sha512', $key, $salt, $iterations, (self::encryptMethodLength() / 4));

            $encryptedString = openssl_encrypt($string, $encryptMethod, hex2bin($hashKey), OPENSSL_RAW_DATA, $iv);

            $encryptedString = base64_encode($encryptedString);
            unset($hashKey);

            $output = ['ciphertext' => $encryptedString, 'iv' => bin2hex($iv), 'salt' => bin2hex($salt), 'iterations' => $iterations];
            unset($encryptedString, $iterations, $iv, $ivLength, $salt);

            return base64_encode(json_encode($output));
        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
        }
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions', 'encrypt256', $eMessage);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }

    /*
     * Method Name: decrypt256
     * Created By : Sharanya T

     * Description:
     * This function is used to decrypt data
     *
     * Input Params:
     * @params $encryptedString, $key
     *
     * Output:
     * Returns decrypted data(String)
     */
    public static function decrypt256($encryptedString='', $key='')
    {
        try {
            $encryptMethod = 'AES-256-CBC';
            if($key != NULL && $encryptedString != ""){
                $json = json_decode(base64_decode($encryptedString), true);

                try {
                    $salt = hex2bin($json["salt"]);
                    $iv = hex2bin($json["iv"]);
                } catch (Exception $e) {
                    return null;
                }
                $cipherText = base64_decode($json['ciphertext']);
                $iterations = intval(abs($json['iterations']));
                if ($iterations <= 0) {
                    $iterations = 999;
                }
                $hashKey = hash_pbkdf2('sha512', $key, $salt, $iterations, (self::encryptMethodLength() / 4));
                unset($iterations, $json, $salt);

                $decrypted= openssl_decrypt($cipherText , $encryptMethod, hex2bin($hashKey), OPENSSL_RAW_DATA, $iv);
                unset($cipherText, $hashKey, $iv);

                return $decrypted;
            }else{
                return "Encrypted String to decrypt, Key is required.";
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
        }
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions', 'decrypt256', $eMessage);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }
    
    public static function decryptRS($encryptedString)
    {
        try {
            $encryptMethod = 'RSA';
            $key  = config('pupvki')['pvt'];
            
            if($key != NULL && $encryptedString != ""){                
                $binEnc = base64_decode($encryptedString);
                //$decrypted = '-1';
                openssl_private_decrypt($binEnc, $decrypted, $key);     
                //openssl_private_decrypt(base64_decode($encData), $decryptedData, $privateKey, OPENSSL_PKCS1_OAEP_PADDING )                           
                if($decrypted != ''){
                    return $decrypted;
                }else{
                    return '__1'.openssl_error_string();
                }
            }else{
                return '__1';
            }
        } catch (\Throwable $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
        }
            $eMessage = $e->getMessage();
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions', 'decryptRS', $eMessage);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }


    /*
    *  Method Name: getAccountsCountByTypeAndStatus
    *  Created By : Sharanya T
    *  Created At : 10-02-2020
    *
    *  Description:
    *  Method to fetch applcaition count based on account type
    *
    *  Params:
    *  @$accountType
    *
    *  Output:
    *  Returns Count.
    */
    public static function getAccountsCountByTypeAndStatus($accountType,$status='')
    {

        $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
        //fetch applcation count based on account type
        $count = DB::table('ACCOUNT_DETAILS')
                        ->where(['ACCOUNT_TYPE'=>array_keys(config('constants.ACCOUNT_TYPES'),$accountType)]);
        // echo "<pre>";print_r($status);exit;        
        if($status != ''){
            $count = $count->whereIn('APPLICATION_STATUS',$status);
        }
        if(Session::get('role') == 2)
        {
            $count = $count->where(['CREATED_BY'=>Session::get('userId')])
                            ->whereIn('APPLICATION_STATUS',[1,2,3,4,6,7,8,9,10,11,13,14]);
        }
        if(Session::get('role') == 8)
        {
            $count = $count->whereIn('APPLICATION_STATUS',[22,23,24,26]);
        }        
        if(Session::get('role') == 13)
        {
            $count = $count->whereIn('APPLICATION_STATUS',[1,2,3,4,6,7,8,9,10,11,13,14,15]);
        }

        if(Session::get('role') == 14){

                    if(!isset($userDetails['filter_type'])){

                        $f_type = '';
                        $f_id = '';

                    }else{

                        $f_type = $userDetails['filter_type'];
                        $f_id = $userDetails['filter_ids'];
                    }

                    $count = $count->leftjoin('BRANCH','BRANCH.BRANCH_ID','ACCOUNT_DETAILS.BRANCH_ID');

                    switch ($f_type){

                        case '1':
                            $count = $count->where('BRANCH.REGION_ID',$f_id);
                            break;

                        case '2':
                            $count = $count->where('BRANCH.ZONE_ID',$f_id);
                            break;

                        case '3':
                            $count = $count->where('BRANCH.CLUSTER_ID',$f_id);
                            break;

                        default:
                            $count = $count->where('ACCOUNT_DETAILS.BRANCH_ID',Session::get('branchId'));
                            break;
                    }

        }else{
            $count = $count;
        }
        $count = $count->count();
        return $count;
    }

    /*
    *  Method Name: getAccountTypes
    *  Created By : Sharanya T
    *  Created At : 20-02-2020
    *
    *  Description:
    *  Method to fetch account types
    *
    *  Params:
    *
    *  Output:
    *  Returns accountTypes.
    */
    public static function getAccountTypes()
    {
        $accountTypes = array();
        $accountTypes = Cache::get('ACCOUNT_TYPES');
        if($accountTypes == '')
        {
            $accountTypes = DB::table('ACCOUNT_TYPES')->orderBy('RANK')->pluck('account_type','id')->toArray();
            Cache::put('ACCOUNT_TYPES',$accountTypes,43200);
        }
        //$accountTypes[2] = 'DBSA (Saving & Current)'; // TBD
        return $accountTypes;
    }


    public static function getAccountlevelTypes()
    {
        $accountlevelTypes = array();
        $accountlevelTypes = Cache::get('ACCOUNT_LEVEL_TYPES');
        if($accountlevelTypes == '')
        {
            $accountlevelTypes = DB::table('ACCOUNT_LEVEL_TYPES')->pluck('account_level_type','id_code')->toArray();
            Cache::put('ACCOUNT_LEVEL_TYPES',$accountlevelTypes,43200);
        }
        return $accountlevelTypes;
    }


    public static function getCustomerDetails($lastMonths=1)
    {
        $customerDetails = array();

       
        $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('ID',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME AS user_name"))
                            ->whereNotNull('LAST_NAME')
                            ->where('CREATED_AT','>=',Carbon::now()->subMonths($lastMonths))
                             ->pluck('user_name','id')->toArray();
        
        return $customerDetails;
    }

    public static function getCustomerDetailsNpc($role)
    {
        $customerDetails = array();
        $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->select('CUSTOMER_OVD_DETAILS.ID',DB::raw("CUSTOMER_OVD_DETAILS.FIRST_NAME || ' ' || CUSTOMER_OVD_DETAILS.MIDDLE_NAME || ' ' || CUSTOMER_OVD_DETAILS.LAST_NAME || '-'|| ACCOUNT_DETAILS.AOF_NUMBER AS user_name"))
                            ->where('ACCOUNT_DETAILS.NEXT_ROLE',$role)
                            ->whereNotIn('ACCOUNT_DETAILS.APPLICATION_STATUS',[5,12,45])
                            ->whereNotNull('CUSTOMER_OVD_DETAILS.LAST_NAME')
                            ->pluck('user_name','id')->toArray();
        return $customerDetails;
    }

    public static function getCustomerDetailsBank($branchId)
    {
        $customerDetails = array();
        $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                            ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->select('CUSTOMER_OVD_DETAILS.ID',DB::raw("CUSTOMER_OVD_DETAILS.FIRST_NAME || ' ' || CUSTOMER_OVD_DETAILS.MIDDLE_NAME || ' ' || CUSTOMER_OVD_DETAILS.LAST_NAME AS user_name"))
                            ->where('ACCOUNT_DETAILS.NEXT_ROLE','<=',5)
                            ->where('ACCOUNT_DETAILS.BRANCH_ID',$branchId)
                            ->whereNotNull('CUSTOMER_OVD_DETAILS.LAST_NAME')
                            ->pluck('user_name','id')->toArray();
        return $customerDetails;
    }


    public static function getCustomerDetailsforCallCenter()
    {
        $customerDetails = array();
        $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')
                            ->select('CUSTOMER_OVD_DETAILS.ID',DB::raw("FIRST_NAME || ' ' || MIDDLE_NAME || ' ' || LAST_NAME AS user_name"))
                             ->leftjoin('ACCOUNT_DETAILS','ACCOUNT_DETAILS.ID','CUSTOMER_OVD_DETAILS.FORM_ID')
                            ->where('ACCOUNT_DETAILS.SOURCE','=', 'CC')
                            ->where('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE',1)
                            ->whereNotNull('LAST_NAME')
                            ->pluck('user_name','id')->toArray();
        return $customerDetails;
    }

    /*
    *  Method Name: getModeOfOperations
    *  Created By : Sharanya T
    *  Created At : 20-02-2020
    *
    *  Description:
    *  Method to fetch Mode Of Operations
    *
    *  Params:
    *
    *  Output:
    *  Returns modeofopertaions.
    */
    public static function getModeOfOperations($accountType='-1',$formId='')
    {
        $modeOfOperations = array();
        // $modeOfOperations = Cache::get('MODE_OF_OPERATIONS');
        // if($modeOfOperations == '')
        // {
        //     $modeOfOperations = DB::table('MODE_OF_OPERATIONS')->pluck('operation_type','id')->toArray();
        //     Cache::put('MODE_OF_OPERATIONS',$modeOfOperations,43200);
        // }
            switch($accountType){
                case '2':

                    $getFlowType = DB::table('ACCOUNT_DETAILS')->select('FLOW_TAG_1')
                                                               ->whereId($formId)
                                                               ->get()
                                                               ->toArray();

                    $getFlowType = (array) current($getFlowType);   
                    
                    if($getFlowType['flow_tag_1'] == 'INDI'){
                $modeOfOperations = DB::table('MODE_OF_OPERATIONS')
                                                                ->where('CODE','001')
                                                                ->where('FILTER','I')
                                                                ->pluck('operation_type','id')->toArray();
                    }else{

                        $modeOfOperations = DB::table('MODE_OF_OPERATIONS')
                                                        ->where('FILTER','P')
                                                        ->pluck('operation_type','id')->toArray();
                    }
                break;

                default:
                $modeOfOperations = DB::table('MODE_OF_OPERATIONS')
                                                        ->where('FILTER','I')
                                                        ->pluck('operation_type','id')->toArray();
                break;

            }
            // echo "<ptre>";print_r($modeOfOperations);exit;
        return $modeOfOperations;
    }

    /*
    *  Method Name: getResidentialStatus
    *  Created By : Sharanya T
    *  Created At : 20-02-2020
    *
    *  Description:
    *  Method to fetch Residential Status
    *
    *  Params:
    *
    *  Output:
    *  Returns residentialStatus.
    */
    public static function getResidentialStatus()
    {
        $residentialStatus = array();
        $residentialStatus = Cache::get('RESIDENTIAL_STATUS');
        if($residentialStatus == '')
        {
            $residentialStatus = DB::table('RESIDENTIAL_STATUS')->pluck('residential_status','id')->toArray();
            Cache::put('RESIDENTIAL_STATUS',$residentialStatus,43200);
        }
        return $residentialStatus;
    }

    public static function getCustomerAccountTypes()
    {
        $customerAccountTypes = array();
        $customerAccountTypes = Cache::get('CUSTOMER_ACCOUNT_TYPES');
        if($customerAccountTypes == '')
        {
            $customerAccountTypes = DB::table('CUSTOMER_ACCOUNT_TYPES')->pluck('account_type','id')->toArray();
            Cache::put('CUSTOMER_ACCOUNT_TYPES',$customerAccountTypes,43200);
        }
        return $customerAccountTypes;
    }

    public static function getMaritalStatus()
    {
        $maritalStatus = array();
        $maritalStatus = Cache::get('MARITAL_STATUS');
        if($maritalStatus == '')
        {
            $maritalStatus = DB::table('marital_status')->pluck('marital_status','id')->toArray();
            Cache::put('MARITAL_STATUS',$maritalStatus,43200);
        }
        return $maritalStatus;
    }

    /*
    *  Method Name: getSchemeCodes
    *  Created By : Sharanya T
    *  Created At : 20-02-2020
    *
    *  Description:
    *  Method to fetch Scheme Codes
    *
    *  Params:
    *
    *  Output:
    *  Returns SchemeCodes.
    */
    public static function getSchemeCodes($accountType)
    {
        $schemeCodes = array();
        $table = "SCHEME_CODES";
        if($accountType == 3)
        {
            $table = "TD_SCHEME_CODES";
        }
        if($accountType == 2)
        {
            $table = "CA_SCHEME_CODES";
        }
        $schemeCodes = DB::table($table)
                            ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->where('IS_ACTIVE',1)
                            ->pluck('scheme_code','id')->toArray();
        
        return $schemeCodes;
    }

    public static function getSchemeCodesBySchemeId($accountType,$schemeId)
    {
        $schemeCodes = array();
        $table = "SCHEME_CODES";
        if($accountType == 3)
        {
            $table = "TD_SCHEME_CODES";
        }
        
        if($accountType == 2){
            $table = "CA_SCHEME_CODES";
        }

        if($accountType == 4){
            $accountType = 1;
        }

        $schemeCodes = DB::table($table)
                            ->whereId($schemeId)
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->get()->toArray();

        if($accountType==2)
        {
            if($schemeId == 14){
                $schemeId = 1;
            }
            $schemeCodes = DB::table('CA_SCHEME_CODES')
                            ->whereId($schemeId)
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->get()->toArray();

        }
        return $schemeCodes;
    }

    public static function gettdSchemeCodes($accountType)
    {
        $schemeCodes = array();
        /*$schemeCodes = Cache::get('SCHEME_CODES');
        if($schemeCodes == '')
        {
            $schemeCodes = DB::table('SCHEME_CODES')
                                            ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                                            ->where('ACCOUNT_TYPE',$accountType)
                                            ->pluck('scheme_code','id')->toArray();
            Cache::put('SCHEME_CODES',$schemeCodes,43200);
        }*/
        $schemeCodes = DB::table('TD_SCHEME_CODES')
                            ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->pluck('scheme_code','id')->toArray();
        return $schemeCodes;
    }


    public static function getSchemeCodebyAccountType($accountType)
    {
        switch($accountType){
            case '3':
                $table = "TD_SCHEME_CODES";
            break;

            case '2':
                $table = "CA_SCHEME_CODES";
            break;

            default:
                $table = "SCHEME_CODES";
            break;
        }
        $TermDepositSchemeCodes = array();
        $schemeCodes = DB::table($table)
                            ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->where('IS_ACTIVE',1);

        if($table == 'TD_SCHEME_CODES'){
            $schemeCodes = $schemeCodes->where('RI_NRI','RI')->pluck('scheme_code','id')->toArray();
        }else{
            $schemeCodes = $schemeCodes->pluck('scheme_code','id')->toArray();
        }

        if(Session::get('role') == "11"){
            $schemeCodes = DB::table($table)
                            ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                            // ->where('TD_RD', '!=', 'RD')
                            ->where('IS_CC','Y')
                            ->where('ACCOUNT_TYPE',$accountType)
                            ->where('IS_ACTIVE',1)
                            ->pluck('scheme_code','id')->toArray();
        }

		foreach($schemeCodes as $key => $value){	// Just show CA229 instead of CA129
			if(substr($value, 0, 5)=='SB129'){
				$schemeCodes[$key] = str_replace('SB129', 'CA229', $value);
			}
		}

        return $schemeCodes;
    }


    public static function hufSchemeCodebyAccountType($accountType)
    {
        switch ($accountType) {
            case '3':
                $table = "TD_SCHEME_CODES";
            break;

            case '2':
                $table = "CA_SCHEME_CODES";
            break;

            default:
                $table = "SCHEME_CODES";
            break;
        }
        $TermDepositSchemeCodes = array();
        $schemeCodes = DB::table($table)
            ->select('id', DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
            ->where('ACCOUNT_TYPE', $accountType)
            ->where('IS_ACTIVE', 1)
            ->where('HUF_SCHEME','Y');

        if ($table == 'TD_SCHEME_CODES') {
            $schemeCodes = $schemeCodes->where('RI_NRI', 'RI')->pluck('scheme_code', 'id')->toArray();
        } else {
            $schemeCodes = $schemeCodes->pluck('scheme_code', 'id')->toArray();
        }

        foreach ($schemeCodes as $key => $value) {	// Just show CA229 instead of CA129
            if (substr($value, 0, 5) == 'SB129') {
				$schemeCodes[$key] = str_replace('SB129', 'CA229', $value);
			}
		}

        return $schemeCodes;
    }

    /*
    *  Method Name: getGpaPlan
    *  Created By : Sharanya T
    *  Created At : 20-02-2020
    *
    *  Description:
    *  Method to fetch Scheme Codes
    *
    *  Params:
    *
    *  Output:
    *  Returns SchemeCodes.
    */


    public static function getGpaPlan()
    {

          $gpaPlan = DB::table('GPA_PLANS')->select('ID',DB::raw("PLAN_NAME   || ' ' || FINACLE_NAME AS plan"))
                                    ->pluck('plan','id')->toArray();

        return $gpaPlan;
    }
    /*
    *  Method Name: getOVDList
    *  Created By : Sharanya T
    *  Created At : 24-02-2020
    *
    *  Description:
    *  Method to fetch OVD List based on Proof Type
    *
    *  Params:
    *
    *  Output:
    *  Returns OVD List.
    */
    public static function getOVDList($proofType,$formId = '')
    {
        $ovdTypes = array();
        $ovdTypes = Cache::get('OVD_TYPES');
        if ($ovdTypes == '') {
            $ovdTypes = self::pregetOVDList($proofType,$formId);
        }
        return $ovdTypes;
    }


    public static function getCustomertype($sehemeId)
    {
        $customerTypes = array();
        $customerTypes = DB::table('CUSTOMER_TYPE')->where(['INDIVIDUAL' => 'Y', 'IS_ACTIVE' => 1])
            ->pluck('description', 'id')->toArray();
        return $customerTypes;
    }
     public static function getBasisCategorisation()
    {
        $categories = array();
        $categories = DB::table('BASIS_CATEGORISATION')->where('INDIVIDUAL',['Y'])
                                                   ->pluck('description','id')->toArray();
        // echo "<pre>";print_r($categories);exit;
        return $categories;
    }

    public static function getBasisCategorisationString($basisID)
    {
        $categories = array();
        $categories = DB::table('BASIS_CATEGORISATION')->where('INDIVIDUAL','Y')
                                                    ->whereIn('ID', explode(',', $basisID))
                                                   ->pluck('description','id')->toArray();
        $categories = implode(',', $categories);
        return $categories;
    }

    public static function getRegional()
    {
        $regional = array();
        $regional = DB::table('REGION_MASTER')->orderBy('id')->pluck('region_name','id')->toArray();
        return $regional;
    }

    public static function getCluster()
    {
        $cluster = array();
        $cluster = DB::table('CLUSTER_MASTER')->orderBy('id')->pluck('cluster_name','id')->toArray();
        return $cluster;
    }

    public static function getZone()
    {
        $zone = array();
        $zone = DB::table('ZONE_MASTER')->orderBy('id')->pluck('zone_name','id')->toArray();
        return $zone;
    }

    public static function getCountry()
    {
        $country = array();
        $country = DB::table('COUNTRIES')->orderBy('id')->pluck('name','id')->toArray();
        return $country;
    }

     public static function getCountryData()
    {
        $country = array();
        $country = DB::table('COUNTRIES')->orderBy('id')->pluck('name','country_code')->toArray();
        return $country;
    }

    public static function getTitles()
    {
        $titles = array();
        $titles = DB::table('TITLE')->where('is_active',1)->pluck('description','id')->toArray();
        return $titles;
    }


    public static function getOccupation($customerType = '')
    {
        $occupation = array();
        if($customerType == 0){
            $occupation = DB::table('OCCUPATION')->whereIn('TYPE', ['Individual', 'Both'])->whereIn('ETB_NTB_BOTH',['BOTH','ETB'])->pluck('description', 'id')->toArray();
        }else{
            $occupation = DB::table('OCCUPATION')->whereIn('TYPE', ['Individual', 'Both'])->whereIn('ETB_NTB_BOTH',['BOTH','NTB'])->pluck('description', 'id')->toArray();
        }
        
        return $occupation;
    }

    public static function getRoles()
    {
        $roles = array();
        $roles = DB::table('USER_ROLES')->pluck('role','id')->toArray();
        return $roles;
    }
    /*
    *  Method Name: getStates
    *  Created By : Sharanya T
    *  Created At : 25-02-2020
    *
    *  Description:
    *  Method to fetch cities by state
    *
    *  Params:
    *
    *  Output:
    *  Returns states.
    */
    public static function getCitiesByStateId($stateId)
    {
        $cities = array();
        $cities = DB::table('CITY')->where('STATE_ID',$stateId)->pluck('city','id')->toArray();
        return $cities;
    }

    public static function getAddressDataByPincode($pincode)
    {
        $addressData = array();
        $addressData = DB::table('FIN_PCS_DESC')->select('COUNTRYDESC','STATEDESC','CITYDESC')
                                                ->where('pincode',$pincode)
                                                ->get()->toArray();
        return $addressData;
    }

    public static function markImage($formId,$image,$signStamp)
    {
        // $formId = "264";
        // $image = "1u3zYiV3g3.png";
        // echo $image;exit;
        $img = Image::make(storage_path('/uploads/attachments/'.$formId.'/'.$image));
        // $img = Image::make(public_path('uploads/attachments/264/1u3zYiV3g3.png'));
        // $img = Image::make(public_path('uploads/temp/'.$formId.'/'.$image));
        // echo "<pre>";print_r($img);exit;
        $dimension = $img->height()+150;
        $width  = $img->width();
        $height = $img->height()+100;
        $img->resize(null, $height, function ($constraint) {
        // $img->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->resizeCanvas(null, $dimension, 'top', false, '#FFFFFF');
        // $img->resizeCanvas($width, $dimension, 'top', true, '#FFFFFF');
        // $img->resizeCanvas(null, $dimension, 'top', true, '#FFFFFF');
        $imgWidth  = $img->width();
        $imgHeight = $img->height();
        // $message = 'OSV done by '.Session::get('username').' on '.Carbon::now()->format('d M Y H:i a');
        if($signStamp == '_STAMP_'){
            $message = 'TimeStamp: '.Carbon::now()->format('d M Y H:i a');
        }else{
            // SIGN
            $message = 'OSV done by '.Session::get('username').' on '.Carbon::now()->format('d M Y H:i a');
        }
        $img->text($message, $imgWidth-250, $imgHeight-25,function($font) {
            $font->file(public_path('fonts/OpenSans-Light.ttf'));
            $font->size(20);
            $font->color('#8B0000');
            $font->align('center');
            $font->valign('bottom');
        });
        $folder = storage_path('/uploads/markedattachments/'.$formId);
        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0775, true, true);
        }
        $imageSaved = $img->save(storage_path('/uploads/markedattachments/'.$formId.'/'.$image));
        return $imageSaved;
    }

    public static function encryptApi($payload)
    {
        //$key = "BECEDE9F047E88314D6A9347B90E2BECF2AF3805F5DCE0DC2DA33713884A1D9A";
        $key = config('constants.APPLICATION_SETTINGS.JWE_ENC_KEY');
        $key = hex2bin($key);
        $key_algo = new A256KWAlgorithm($key);
        $enc_algo = new A128CBCHS256Algorithm();
        // DEF as a compression algorithm
        $zip_algo = new DeflateAlgorithm();
        // encrypt payload to produce JWE
        $jwe = JWE::encrypt($payload, $key_algo, $enc_algo, $zip_algo);
        $jwe = explode('.', $jwe);
        $response = array();
        $response['recipients'][]['encrypted_key'] = $jwe[1];
        $response['protected'] = $jwe[0];
        $response['iv'] = $jwe[2];
        $response['ciphertext'] = $jwe[3];
        $response['tag'] = $jwe[4];
        return json_encode($response);       // echo "<pre>";print_r(json_encode($response));exit;
    }


    public static function decryptApi($decodeString)
    {
        $response = json_decode($decodeString,true);
        $string = $response['protected'].'.'.$response['recipients'][0]['encrypted_key'].'.'.$response['iv'].'.'.
                    $response['ciphertext'].'.'.$response['tag'];
        //$key = "BECEDE9F047E88314D6A9347B90E2BECF2AF3805F5DCE0DC2DA33713884A1D9A";
        $key = config('constants.APPLICATION_SETTINGS.JWE_ENC_KEY');
        $key = hex2bin($key);
        // create a JSON Web Key from password
        $jwk = SymmetricKeyJWK::fromKey($key);
        // read JWE token from the first argument
        $jwe = JWE::fromCompact($string);
        // decrypt the payload using a JSON Web Key
        $payload = $jwe->decryptWithJWK($jwk);
        return json_decode($payload,true);
    }

    public static function saveStatusDetails($formId,$status,$comments='')
    {
        $currentDate = Carbon::now()->format('Ymd');
        $saveStatus = 0;
        if (($formId == '') && (Session::get('randomString') == '')) {
            $formId = $currentDate.mt_rand();
            session::put('randomString', $formId);
        }
        if ($formId == '') {
            $formId = Session::get('randomString');
        }

        if ($comments == 'done by cron') {
            $created_by = 1;
            $role = 1;
        } else {
            $created_by = Session::get('userId');
            $role = Session::get('role');
        }
        if($role == '11'){
            $role = '3';
        }

        $statusArray = [
            'FORM_ID' => $formId,
                        'ROLE' => $role,
                        'STATUS' => $status,
                        'COMMENTS' => $comments,
            'CREATED_BY' => $created_by
        ];

        $saveStatus = DB::table('STATUS_LOG')->insert($statusArray);
        return $saveStatus;
    }

    public static  function saveAmendStatusLog($crfNumber,$status,$role,$comments=''){
        $status = Self::getAmendStatusId($status);
        $statusLogArray = ['CRF_NUMBER' => $crfNumber,'STATUS' => $status, 'ROLE' => $role];
        $getAmendStatusDetails = DB::table('AMEND_STATUS_LOG')->where($statusLogArray);
        $saveAmendMaster = false;
        if($getAmendStatusDetails->count() < 1){
            $statusLogArray['COMMENTS'] = $comments;
            $statusLogArray['CREATED_BY'] = Session::get('userId');
            $saveAmendStatus = DB::table('AMEND_STATUS_LOG')->insert($statusLogArray);
            if($saveAmendStatus){
                $saveAmendMaster = DB::table('AMEND_MASTER')->where('CRF_NUMBER',$crfNumber)->update(['CRF_STATUS' => $status]);
            }
        }
        return $saveAmendMaster;

    }

    public static function getAmendStatusId($status){
        $getConfigStatus = config('amend_status');
        $getConfigStatus = array_flip($getConfigStatus['CRF_STATUS']);
        $status = $getConfigStatus[$status];
        return $status;
    }

    public static function getFormDetails($formId)
    {
        $userDetails = array();
        $delightDetails = array();
        $files = array();
        $accountDetails = DB::table('ACCOUNT_DETAILS')
                                ->select('ACCOUNT_DETAILS.*','ACCOUNT_TYPES.ID as ACCOUNT_TYPE_ID','ACCOUNT_TYPES.ACCOUNT_TYPE',
                                            'MODE_OF_OPERATIONS.OPERATION_TYPE as MODE_OF_OPERATION','ACCOUNT_DETAILS.SCHEME_CODE',
                                            'TD_SC.SCHEME_CODE as TD_SCHEME_CODE','TDSC.SCHEME_CODE as TDSCHEME_CODE',
                                            'GPA_PLANS.PLAN_NAME as GPAPLAN','SEGMENT.SEGMENT_VALUE AS SEGMENT_CODE','CARD_RULES.DESCRIPTION as CARD_DESCRIPTION' )
                                ->leftjoin('ACCOUNT_TYPES','ACCOUNT_TYPES.ID','ACCOUNT_DETAILS.ACCOUNT_TYPE')
                                ->leftjoin('MODE_OF_OPERATIONS','MODE_OF_OPERATIONS.ID','ACCOUNT_DETAILS.MODE_OF_OPERATION')
                                // ->leftjoin('SCHEME_CODES','SCHEME_CODES.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                ->leftjoin('TD_SCHEME_CODES as TDSC','TDSC.ID','ACCOUNT_DETAILS.SCHEME_CODE')
                                ->leftjoin('TD_SCHEME_CODES as TD_SC','TD_SC.ID','ACCOUNT_DETAILS.TD_SCHEME_CODE')
                                ->leftjoin('GPA_PLANS','GPA_PLANS.ID','ACCOUNT_DETAILS.GPAPLAN')
                                ->leftjoin('SEGMENT','SEGMENT.SEGMENT_CODE','ACCOUNT_DETAILS.SEGMENT_CODE')
                                ->leftjoin('CARD_RULES', 'CARD_RULES.ID','ACCOUNT_DETAILS.CARD_TYPE')
                                ->where('ACCOUNT_DETAILS.ID',$formId)
                                ->get()->toArray();
        $accountDetails = (array) current($accountDetails);
            $schemeCode = DB::table('ACCOUNT_DETAILS')->select('SCHEME_CODE')->whereId($formId)->get()->toArray(); 
            $schemeCode = (array) current($schemeCode);
            if($accountDetails['account_type'] == 'Current' && $accountDetails['scheme_code'] == 14){
                $schemeCode['scheme_code'] = 1;
            }

            $currentSchemeCode = Self::getSchemeCodesBySchemeId($accountDetails['account_type_id'],$schemeCode['scheme_code']);
            $currentSchemeCode = (array) current($currentSchemeCode);
            $accountDetails['scheme_code'] = $currentSchemeCode['scheme_code'];

        $accountDetails['other_proof'] = explode(',', $accountDetails['other_proof']);

        $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                    ->select('CUSTOMER_OVD_DETAILS.*','RESIDENTIAL_STATUS.RESIDENTIAL_STATUS',
                                        'OVD_TYPES.OVD as PROOF_OF_IDENTITY','A.OVD as PROOF_OF_ADDRESS','CA.OVD as PROOF_OF_CURRENT_ADDRESS',
                                        'BANK.BANK_NAME as BANK_NAME','TITLE.DESCRIPTION as TITLE','COUNTRIES.NAME as PER_COUNTRY',
                                        'CC.NAME as CURRENT_COUNTRY','MARITAL_STATUS.MARITAL_STATUS as MARITAL_STATUS',
                                        'RELATIONSHIP.DISPLAY_DESCRIPTION as RELATIONSHIP','MB.BANK_NAME as MATURITY_BANK_NAME','IS_NEW_CUSTOMER')
                                    ->leftjoin('RESIDENTIAL_STATUS','RESIDENTIAL_STATUS.ID','CUSTOMER_OVD_DETAILS.RESIDENTIAL_STATUS')
                                    ->leftjoin('OVD_TYPES','OVD_TYPES.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_IDENTITY')
                                    ->leftjoin('OVD_TYPES as A','A.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_ADDRESS')
                                    ->leftjoin('OVD_TYPES as CA','CA.ID','CUSTOMER_OVD_DETAILS.PROOF_OF_CURRENT_ADDRESS')
                                    ->leftjoin('BANK','CUSTOMER_OVD_DETAILS.BANK_NAME','BANK.ID')
                                    ->leftjoin('BANK as MB','CUSTOMER_OVD_DETAILS.MATURITY_BANK_NAME','MB.ID')
                                    ->leftjoin('TITLE','CUSTOMER_OVD_DETAILS.TITLE','TITLE.ID')
                                    ->leftjoin('COUNTRIES','CUSTOMER_OVD_DETAILS.PER_COUNTRY','COUNTRIES.ID')
                                    ->leftjoin('COUNTRIES as CC','CUSTOMER_OVD_DETAILS.CURRENT_COUNTRY','CC.ID')
                                    ->leftjoin('MARITAL_STATUS','CUSTOMER_OVD_DETAILS.MARITAL_STATUS','MARITAL_STATUS.ID')
                                    ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','CUSTOMER_OVD_DETAILS.RELATIONSHIP')
                                    ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
									->orderBy('CUSTOMER_OVD_DETAILS.APPLICANT_SEQUENCE','ASC')
                                    ->get()->toArray();

        $customerOvd = (array) current($customerOvdDetails);

        $entityDetails = DB::table('ENTITY_DETAILS')
                                        ->leftjoin('COUNTRIES','ENTITY_DETAILS.ENTITY_COUNTRY','COUNTRIES.ID')
                                        ->leftjoin('OVD_TYPES','OVD_TYPES.ID','ENTITY_DETAILS.PROOF_OF_ENTITY_ADDRESS')
                                        ->where('ENTITY_DETAILS.FORM_ID',$formId)
                                        ->orderBy('ENTITY_DETAILS.APPLICANT_SEQUENCE','ASC')
                                        ->get()->toArray();
        $entityDetails = (array) current($entityDetails);


        $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')
                                        ->select('RISK_CLASSIFICATION_DETAILS.*','CUSTOMER_TYPE.DESCRIPTION as CUSTOMER_TYPE',
                                            'COUNTRIES.NAME as COUNTRY_NAME','OCCUPATION.DESCRIPTION as OCCUPATION',
                                            'CB.NAME as COUNTRY_OF_BIRTH','C.NAME as CITIZENSHIP','RE.NAME as RESIDENCE')
                                        ->leftjoin('CUSTOMER_TYPE','CUSTOMER_TYPE.ID','RISK_CLASSIFICATION_DETAILS.CUSTOMER_TYPE')
                                        ->leftjoin('COUNTRIES','COUNTRIES.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_NAME')
                                        ->leftjoin('COUNTRIES as CB','CB.ID','RISK_CLASSIFICATION_DETAILS.COUNTRY_OF_BIRTH')
                                        ->leftjoin('COUNTRIES as C','C.ID','RISK_CLASSIFICATION_DETAILS.CITIZENSHIP')
                                        ->leftjoin('COUNTRIES as RE','RE.ID','RISK_CLASSIFICATION_DETAILS.RESIDENCE')
                                        ->leftjoin('OCCUPATION','OCCUPATION.ID','RISK_CLASSIFICATION_DETAILS.OCCUPATION')
                                        ->where('FORM_ID',$formId)
                                        ->orderBy('RISK_CLASSIFICATION_DETAILS.APPLICANT_SEQUENCE','ASC')
                                        ->get()->toArray();

        $nomineeDetails = DB::table('NOMINEE_DETAILS')
                                    ->select('NOMINEE_DETAILS.*','RELATIONSHIP.DISPLAY_DESCRIPTION as RELATINSHIP_APPLICANT',
                                        'REL.DISPLAY_DESCRIPTION as RELATINSHIP_APPLICANT_GUARDIAN',
                                                                                        'COUNTRIES.NAME as NOMINEE_COUNTRY','COUNTRIES.NAME as GUARDIAN_COUNTRY')
                                     ->leftjoin('RELATIONSHIP','RELATIONSHIP.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT')
                                     ->leftjoin('RELATIONSHIP as REL','REL.ID','NOMINEE_DETAILS.RELATINSHIP_APPLICANT_GUARDIAN')
                                    ->leftjoin('COUNTRIES','COUNTRIES.ID','NOMINEE_DETAILS.NOMINEE_COUNTRY')
                                    ->where('FORM_ID',$formId)->orderBy('NOMINEE_DETAILS.ID','ASC')->get()->toArray();
        // $nomineeDetails = (array) current($nominesssseDetails);
 
        $initial_funding_type = $customerOvdDetails[0]->initial_funding_type;
        // $customerOvdDetails['initial_funding_type']
        if((Session::get('customer_type') == "ETB") && ($initial_funding_type == 3) && $accountDetails['source'] == 'CC')
        {
            $accountType = 1000;
        }else{
            $accountType = $accountDetails['account_type_id'];
        }

        if($customerOvd['self_thirdparty'] == 'thirdparty'){
            if($accountType == 1){
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                  //->whereIn("ID",[1,2,3,4,14,22,23,24])
                                                                  ->get()->toArray();
                
            }elseif($accountType == 3){
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                   //->whereIn("ID",[9,10,11,12,15,25,26,27])
                                                                   ->get()->toArray();
            }elseif($accountType == 4){
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                   //->whereIn("ID",[5,6,7,8,16,28,29,30])
                                                                   ->get()->toArray();
            }else{
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)->get()->toArray();
            }
        }elseif($customerOvd['self_thirdparty'] != 'thirdparty'){
            if($accountType == 1){
            $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                    ->where("ID",'!=',14)
                                                                    ->get()->toArray();
            }elseif($accountType == 3){
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                   ->where("ID",'!=',14)
                                                                   ->get()->toArray();
            }elseif($accountType == 4){
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)
                                                                   ->where("ID",'!=',14)
                                                                   ->get()->toArray();
            }else{
              $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)->get()->toArray();
            }
        }else{
            $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE",$accountType)->get()->toArray();
        }

        if(($accountType == 1) && ($accountDetails['delight_scheme'] == 5))
        {
            if ($customerOvd['self_thirdparty'] == 'thirdparty') {
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE", 5)
                                                                   //->whereIn("ID",[17,18,19,20,21,31,32,33])
                                                                   ->get()->toArray();
            }else{
                $declarationsList = DB::table("SUBMISSION_DECLARATIONS")->where("ACCOUNT_TYPE", 5)
                                                                   //->whereIn("ID",[17,18,19,20,31,32,33])
                                                                       ->where("ID",'!=',21)
                                                                       ->get()->toArray();
            }

            $delightKitDetails = DB::table('DELIGHT_KIT')->whereId($accountDetails['delight_kit_id'])
                                                            ->get()->toArray();
            $delightDetails = (array) current($delightKitDetails);
        }


		// Get all type of Image Data, first array is list of names, second is an object with metadata
		$imageData = Self::getValidImageNames($formId);
		if(isset($imageData[0])){
			$validImagesName = $imageData[0];
			foreach ($validImagesName as &$imageName) {
				$imageName = str_replace('_DONOTSIGN_', '', $imageName);
			}
		}else{
			$validImagesName = [];
		}

		$folder = Self::getImagePath($formId);

		// Check if $folder exists first // For TD-ETB there may not be any file to process!
		if(file_exists($folder)){
			$filesInFolder = \File::files($folder);
			foreach($filesInFolder as $path) {
					$file = pathinfo($path);

					// Remove any tags and compare
					$physicalFile = str_replace('_DONOTSIGN_','',$file['filename'].'.'.$file['extension']);

					// Push only valid files that are found in DB
					if(in_array($physicalFile, $validImagesName)){
						$filename['filename'] = $file['filename'].'.'.$file['extension'];
						$filename['time'] = filemtime($path);
						array_push($files, $filename);
					}
			}
			$files = collect($files)->sortBy('time')->toArray();
        }

		$custDeclarations = DB::table('CUSTOMER_DECLARATIONS')
                                            ->where(['FORM_ID'=>$formId,'IS_ACTIVE'=>1])->get()->toArray();
		for($f = 0; $f < count($files); $f++){
			$fname = $files[$f]['filename'];
			$files[$f]['type']='id_ovd_ip_sign'; // defa
			for($d=0; $d < count($custDeclarations); $d++){
				if($custDeclarations[$d]->attachment == $fname){
					$files[$f]['type']='declaration';
				}
			}
		}


        if(count($customerOvdDetails) > 0)
        {
            $i = 0;
            foreach ($customerOvdDetails as $customerDetails) {
                $userDetails[$i]['customerOvdDetails'] = $customerDetails;
                if(isset($riskDetails[$i]) && $riskDetails[$i] != ''){
                    
                $userDetails[$i]['riskDetails'] = $riskDetails[$i];
                }
                
                $i++;
            }
        }

        
        if(count($riskDetails)){
            for($i=0;count($riskDetails)>$i;$i++){
                $sourceoffund = explode(',',$riskDetails[$i]->source_of_funds);
                $getdescsourceoffund = DB::table('SOURCE_OF_FUNDS')->select('SOURCE_OF_FUND')->whereIn('ID',$sourceoffund)->get()->toArray();
                $sourceoffunddesc = array();
                for($j=0;count($getdescsourceoffund)>$j;$j++){
                    $sourceoffunddesc[$j]= $getdescsourceoffund[$j]->source_of_fund;
                }
                $riskDetails[$i]->source_of_funds = implode(',',$sourceoffunddesc);
            }
        }
        if ($accountDetails['source'] == 'CC') {
            for ($i = 0; $i < count($declarationsList); $i++) {
                $declarationCC = (array) $declarationsList[$i];
                $declarationCheck = array_search('2022-10-20 17:26:43', $declarationCC);
                if ($declarationCheck == 'created_at') {
                    unset($declarationsList[$i]);
                }
            }
        }

        $response = [
            'accountDetails' => $accountDetails,
            'userDetails' => $userDetails,
            'nomineeDetails' => $nomineeDetails,
            'customerOvdDetails' => $customerOvdDetails,
            'entityDetails' => $entityDetails,
            'declarationsList' => $declarationsList,
            'files' => $files,
            'custDeclarations' => $custDeclarations,
            'delightDetails' => $delightDetails
        ];



		return $response;
    }

    // Return imageFolder path based on role, is_review and formId
	public static function getImagePath($formId){

		// if in review
		if(Session::get('is_review') == 1){
            return $folder = storage_path('/uploads/markedattachments/'.$formId);
        }

        $currRole = Session::get('role');

		switch($currRole){
			case '2': // Branch Sales - Submitted or not submitted
				if(file_exists(storage_path('/uploads/attachments/'.$formId.'/signed'))){
					return storage_path('/uploads/markedattachments/'.$formId);
				}else{
					return storage_path('/uploads/attachments/'.$formId);
				}
				break;
			default:
				return storage_path('/uploads/markedattachments/'.$formId);
				break;
		}
		return storage_path('/uploads/markedattachments/'.$formId);

	}

    public static function getImagePublicPath($formId){
		// if in review
		if(Session::get('is_review') == 1){
            return '/imagesmarkedattachments/'.$formId;
        }
        $currRole = Session::get('role');
		switch($currRole){
			case '2': // Branch Sales - Submitted or not submitted
				if(file_exists(storage_path('/uploads/attachments/'.$formId.'/signed'))){
					return '/imagesmarkedattachments/'.$formId;
				}else{
					return '/imagesattachments/'.$formId;
				}
				break;
			default:
				return '/imagesmarkedattachments/'.$formId;
				break;
		}
		return '/imagesmarkedattachments/'.$formId;
	}

    public static function getValidImageNames($formId)
    {
        $imageArray = (array) null;
		$imageObject = (array) null;
		$imageMapTable = (array) null;

		$acct = DB::table('ACCOUNT_DETAILS')->where('ID', $formId)->get()->toArray();
		if(count($acct)>0){
			if(isset($acct[0]->customers_photograph) && $acct[0]->customers_photograph != null){
				array_push($imageArray, $acct[0]->customers_photograph);
				$imageMapTable[$acct[0]->customers_photograph] = 'account_details@customers_photograph';
				$imageObject['account']['photo'] = $acct[0]->customers_photograph;
			}
		}

		$ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $formId)->get()->toArray();
		if(count($ovd)>0) {
            for ($o = 0; $o < count($ovd); $o++) {
                if (isset($ovd[$o]->pf_type_image) && $ovd[$o]->pf_type_image != null) {
                    array_push($imageArray, $ovd[$o]->pf_type_image);
                    $imageMapTable[$ovd[$o]->pf_type_image] = 'customer_ovd_details@pf_type_image';
                    $imageObject['cust'][$o]['pf'] = $ovd[0]->pf_type_image;
                }

				if (isset($ovd[$o]->id_proof_image) && $ovd[$o]->id_proof_image != null) {
					$idimg = explode(',', $ovd[$o]->id_proof_image);
					if (isset($idimg[0]) && $idimg[0] != '' && $idimg[0] != null) {
						array_push($imageArray, $idimg[0]);
						$imageMapTable[$idimg[0]] = 'customer_ovd_details@id_proof_image';
						$imageObject['cust'][$o]['id_front'] = $idimg[0];
					}
					if (isset($idimg[1]) && $idimg[1] != '' && $idimg[1] != null) {
						array_push($imageArray, $idimg[1]);
						$imageMapTable[$idimg[1]] = 'customer_ovd_details@id_proof_image';
						$imageObject['cust'][$o]['id_back'] = $idimg[1];
					}
				}

				if (isset($ovd[$o]->add_proof_image) && $ovd[$o]->add_proof_image != null) {
					$idimg = explode(',', $ovd[$o]->add_proof_image);
					if (isset($idimg[0]) && $idimg[0] != '' && $idimg[0] != null) {
						array_push($imageArray, $idimg[0]);
						$imageMapTable[$idimg[0]] = 'customer_ovd_details@add_proof_image';
						$imageObject['cust'][$o]['add_front'] = $idimg[0];
					}
					if (isset($idimg[1]) && $idimg[1] != '' && $idimg[1] != null) {
						array_push($imageArray, $idimg[1]);
						$imageMapTable[$idimg[1]] = 'customer_ovd_details@add_proof_image';
						$imageObject['cust'][$o]['add_back'] = $idimg[1];

					}
				}

				if (isset($ovd[$o]->current_add_proof_image) && $ovd[$o]->current_add_proof_image != null) {
					$idimg = explode(',', $ovd[$o]->current_add_proof_image);
					if (isset($idimg[0]) && $idimg[0] != '' && $idimg[0] != null) {
						array_push($imageArray, $idimg[0]);
						$imageMapTable[$idimg[0]] = 'customer_ovd_details@current_add_proof_image';
						$imageObject['cust'][$o]['curr_add_front'] = $idimg[0];
					}
					if (isset($idimg[1]) && $idimg[1] != '' && $idimg[1] != null) {
						array_push($imageArray, $idimg[1]);
						$imageMapTable[$idimg[1]] = 'customer_ovd_details@current_add_proof_image';
						$imageObject['cust'][$o]['curr_add_back'] = $idimg[1];

					}
				}

			} // EndFor each OVD applicant

            if (isset($ovd[0]->cheque_image) && $ovd[0]->cheque_image != null) {
                array_push($imageArray, $ovd[0]->cheque_image);
                $imageMapTable[$ovd[0]->cheque_image] = 'customer_ovd_details@cheque_image';
                $imageObject['cust'][$o]['cheque'] = $ovd[0]->cheque_image;
            }

		} // End -- if(count($ovd)>0) {
        $entity = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
        if(count($entity) > 0){
            if(isset($entity[0]->entity_add_proof_image) && $entity[0]->entity_add_proof_image != null){
                array_push($imageArray, $entity[0]->entity_add_proof_image);
                $imageMapTable[$entity[0]->entity_add_proof_image] = 'entity_details@entity_add_proof_image';
                $imageObject['entity']['img'] = $entity[0]->entity_add_proof_image;
            }
        }

		$nom = DB::table('NOMINEE_DETAILS')->where('FORM_ID', $formId)->get()->toArray();
        if(count($nom)>0){
			if(isset($nom[0]->witness1_signature_image) && $nom[0]->witness1_signature_image != null){
				array_push($imageArray, $nom[0]->witness1_signature_image);
				$imageMapTable[$nom[0]->witness1_signature_image] = 'nominee_details@witness1_signature_image';
				$imageObject['account']['nom_witness'] = $nom[0]->witness1_signature_image;
			}
			if(isset($nom[0]->lti_declaration_image) && $nom[0]->lti_declaration_image != null){
				array_push($imageArray, $nom[0]->lti_declaration_image);
				$imageMapTable[$nom[0]->lti_declaration_image] = 'nominee_details@lti_declaration_image';
				$imageObject['account']['lti_decl'] = $nom[0]->lti_declaration_image;
			}
		}

		$decl = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID', $formId)->get()->toArray();
		if(count($decl)>0){
			for($d=0; $d<count($decl); $d++){
				if(isset($decl[$d]->attachment) && $decl[$d]->attachment != null){
					array_push($imageArray, $decl[$d]->attachment);
					$imageMapTable[$decl[$d]->attachment] = 'customer_declarations@attachment';
					$imageObject['account']['declaration'][$d] = $decl[$d]->attachment;
				}
			}
		}

		return [$imageArray, $imageObject, $imageMapTable];
	}


	public static function updateApplicationStatus($column,$formId)
    {
        $statusDetails = DB::table('STATUS_LOG')->where(['FORM_ID' => $formId])
                                                            ->orderBy('CREATED_AT','DESC')
                                                            ->pluck('created_at')->toArray();
        if(!isset($statusDetails[0]) || !isset($statusDetails[1])){   // DSA Cases 
            $startTime = Carbon::now();
            $finishTime = Carbon::now();
        }else{
            $startTime = Carbon::parse($statusDetails[0]);
            $finishTime = Carbon::parse($statusDetails[1]);
        } 
        $duration = $finishTime->diffInMinutes($startTime);
        $submissionDate = self::getCurrentDBtime();

        $updateDetails = Array('SUBMISSION_DATE'=>$submissionDate,
                                'FORM_ID'=>$formId,
                                $column=>$duration,
                                'BRANCH_ID' => Session::get('branchId'),
								'CREATED_BY' => Session::get('userId')
								);
        $updateApplicationStatus = DB::table('ACCOUNT_STATUS_UPDATE_METRICS')->insert($updateDetails);
        return $updateApplicationStatus;
    }

    public static function saveApiRequest($serviceName,$serviceUrl,$serviceRequest,$serviceResponse,$decryptedServiceRequest,
                                                                            $decryptedServiceResponse,$formId='', $responseTime=0)
    {
        try{
            if(Session::get('userId') != ''){
                $userId = Session::get('userId');
            }
            else{
	          $userId = 1;
            }

            if(strlen($decryptedServiceRequest)>3999){
                $sRequest = substr($decryptedServiceRequest, 0, 3999);
                $sResponse = json_encode($decryptedServiceResponse).' _FOURKREQUEST_: '.$decryptedServiceRequest;
            }else{
                $sRequest = $decryptedServiceRequest;
                $sResponse = json_encode($decryptedServiceResponse);
            }
            $encryptedServiceData = ['SERVICE_NAME'=>$serviceName,
                                    'SERVICE_URL'=>$serviceUrl,
                                    'SERVICE_REQUEST'=>$sRequest,
                                    'SERVICE_RESPONSE'=>$sResponse,
                                    'CREATED_BY'=>$userId,
                                    'FORM_ID'=>$formId,
									'API_RESPONSE_TIME'=>$responseTime,
                                    'BRANCH_ID' => Session::get('branchId') ];
            $saveApiService = DB::table('ENCRYPTED_API_SERVICE_LOG')->insert($encryptedServiceData);
            DB::commit();
            return $saveApiService;
	    }catch(\Illuminate\Database\QueryException $e) {
			dd($e->getMessage());
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage,'',$eMessage);
            return false;
        }
    }

    /*
     * Method Name: getusers
     * Created By : Sharanya T

     * Description:
     * This function is used to get all users
     *
     * Input Params:
     *
     * Output:
     * Returns UsersList(Array)
    */
    public static function getusers()
    {
    
        $users = DB::table('USERS')
                    ->select('ID',DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"))
                    ->pluck('emp_name','id')->toArray();
        return $users;
    }

    public static function getuamusers($data = '')
    {
        $users = DB::table('USERS')
                    ->select('ID','EMPLDAPUSERID AS EMP_NAME')
                    ->where('ID','!=', Session::get('userId'))
                    ->where('EMPSTATUS','!=','D');
                if($data != ''){
                  $users = $users->where('ROLE',$data);
                }
                $users = $users->pluck('emp_name','id')->toArray();
        //echo "<pre>";print_r(count($users));exit;
        return $users;
    }


    public static function getdeleteuamusers($lastDate)
    {
        $users = DB::table('USERS')
                    ->select('ID','EMPLDAPUSERID AS EMP_NAME')
                    ->where('ID','!=', Session::get('userId'))
                    ->where('UPDATED_AT', '>=', now()->subDays($lastDate)) 
                    ->where('EMPSTATUS','D')
                    ->pluck('emp_name','id')->toArray();
        return $users;
    }


      //------------------------------Function will remove after check 19-FEB-2021------------------//

    // public static function accountBalanceEnquiry($customerId,$accountNumber)
    // {
    //     $accountDetails = array();
    //     $url = config('constants.APPLICATION_SETTINGS.ACCOUNT_BALANCE_ENQUIRY_URL');
    //     $client_id = config('constants.APPLICATION_SETTINGS.CLIENT_ID');
    //     $client_key = config('constants.APPLICATION_SETTINGS.CLIENT_KEY');
    //     $authorization = config('constants.APPLICATION_SETTINGS.AUTHORIZATION');
    //     $payload = json_encode(['customerID'=>$customerId,'accountNumber'=>$accountNumber]);
    //     $requestID = EncryptDecrypt::JWEEncryption($payload);
    //     // echo "<pre>";print_r($requestID);exit;
    //     $client = new \GuzzleHttp\Client();
    //     $guzzleClient = $client->request('POST',$url,
    //                                         [   'headers' =>[
    //                                                 'Content-Type'=>'application/json',
    //                                                 'X-IBM-Client-secret'=>$client_key,
    //                                                 'X-IBM-Client-Id'=>$client_id,
    //                                                 'authorization'=>$authorization
    //                                             ],
    //                                             'json'=>[$requestID],
    //                                             'exceptions'=>false
    //                                         ]);
    //     //fetching response from server
    //     $response = json_encode(json_decode($guzzleClient->getBody()));
    //     echo "<pre>";print_r($response);exit;
    //     $accountDetails = EncryptDecrypt::JWEDecryption($response);
    //     echo "<pre>";print_r($accountDetails);exit;
    //     $saveService = CommonFunctions::saveApiRequest('ACCOUNT_BALANCE_ENQUIRY',$url,$requestID,$response);
    //     return $accountDetails;
    // }

    public static function isDangerous($inputstring,$type)
    {
        $dangerous = true;
        $response = preg_replace(config('constants.REGEX.'.$type), '', $inputstring);
        if(strlen($inputstring) == strlen($response)){
            $dangerous = false;
        }
        return $dangerous;
    }

    public static function saveTDAccountDetails($formId)
    {
        // echo $formId;
        $finacle_table = env('FINACLE_TABLE');

        $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
        $accountDetails = (array) current($accountDetails);
        $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
        $nomineeDetails = (array) current($nomineeDetails);
        $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                               ->orderBy('ID','ASC')
																//->where('APPLICANT_SEQUENCE',1)
																->get()->toArray();
        //$customerOvdData = (array) current($customerOvdDetails);
                        //Looping Data
        $finacleQueryInstance = DB::connection('oracle2');
        // $batchNoDetails = $finacleQueryInstance->select('select CUSTOM.DCB_CMUPL_BATCH_NUM.nextVal from dual');
        $batchNoDetails = $finacleQueryInstance->select('select '.env('FINACLE_BATCH_SEQUNCE').'.nextVal from dual');
        $batchNoDetails = (array) current($batchNoDetails);
        $batch_no = $batchNoDetails['nextval'];

		$userDetails = CommonFunctions::getUserDetails($accountDetails['created_by']);
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $empHRMSnumber = $userDetails['hrmsno'];
        }else{
            $empHRMSnumber = '0001';
        }
				
   if(count($customerOvdDetails) > 0){

    foreach ($customerOvdDetails as $key => $customerOvdData) {
    

		if($customerOvdData->account_number == '' && $customerOvdData->maturity_account_number != ''){
			$operativeAccount = $customerOvdData->maturity_account_number;
		}else{
			$operativeAccount = $customerOvdData->account_number;
		}
		if($customerOvdData->maturity_account_number == '' && $customerOvdData->account_number != ''){
			$benefAccount = $customerOvdData->account_number;
			$benefAccountName = $customerOvdData->account_name;
			$benefIfsc = $customerOvdData->ifsc_code;			
		}else{
			$benefAccount = $customerOvdData->maturity_account_number;
			$benefAccountName = $customerOvdData->maturity_account_name;
			$benefIfsc = $customerOvdData->maturity_ifsc_code;			
		}	
        
		if($accountDetails['account_type'] == 4){ // If combo then id stored in td_scheme_code field else in scheme_code
           $chemeCodeDetails = DB::table('TD_SCHEME_CODES')->whereId($accountDetails['td_scheme_code'])->get()->toArray();
        }else{
           $chemeCodeDetails = DB::table('TD_SCHEME_CODES')->whereId($accountDetails['scheme_code'])->get()->toArray();
        }
        $chemeCodeDetails = (array) current($chemeCodeDetails);
        $modDetails = DB::table('MODE_OF_OPERATIONS')->whereId($accountDetails['mode_of_operation'])->get()->toArray();
        $modDetails = (array) current($modDetails);


        /*===========FOR Nominee Country============================*/
        if(isset($nomineeDetails['nominee_country']) && ($nomineeDetails['nominee_country'] != null)){
            $countryCode = DB::table('COUNTRIES')->where('ID',$nomineeDetails['nominee_country'])
                                                     ->get()->toArray();
            $countryCode = (array) current($countryCode);
            $countryCode = $countryCode['country_code'];
        }else{
            $countryCode = '';
        }

        /*===========FOR Nominee Relationship============================*/

        if(isset($nomineeDetails['relatinship_applicant']) && ($nomineeDetails['relatinship_applicant'] != null)){

            $relationCode = DB::table('RELATIONSHIP')->where('ID',$nomineeDetails['relatinship_applicant'])
                                                     ->get()->toArray();
            $relationCode = (array) current($relationCode);
            $relationCode = $relationCode['code'];
        }else{
            $relationCode = '';
        }

        /*===========FOR Guardian Country============================*/
        if(isset($nomineeDetails['guardian_country']) && ($nomineeDetails['guardian_country'] != null)){

            $guardiancountryCode = DB::table('COUNTRIES')->where('ID',$nomineeDetails['guardian_country'])
                                                     ->get()->toArray();
            $guardiancountryCode = (array) current($guardiancountryCode);
            $guardiancountryCode = $guardiancountryCode['country_code'];
        }else{
            $guardiancountryCode = '';
        }

	    $fundingDetails = DB::table('FINCON')->where('FORM_ID',$formId)->get()->toArray();
        $fundingDetails = (array) current($fundingDetails);

        /*===========FOR Nominee Pincode============================*/
        if(isset($nomineeDetails['nominee_pincode']) && ($nomineeDetails['nominee_pincode'] != null)){

            $nominee_pcsDetails = DB::table('FIN_PCS_DESC')->where('pincode',$nomineeDetails['nominee_pincode'])->get()->toArray();
            $nominee_pcsDetails = (array) current($nominee_pcsDetails);

        }else{
           $nominee_pcsDetails['citycode'] = '';
           $nominee_pcsDetails['statecode'] = '';
        }

        /*===========FOR Guardian Pincode============================*/
        if((isset($nomineeDetails['guardian_pincode'])) && ($nomineeDetails['guardian_pincode'] != null)){

            $guardian_pcsDetails = DB::table('FIN_PCS_DESC')->where('pincode',$nomineeDetails['guardian_pincode'])->get()->toArray();
            $guardian_pcsDetails = (array) current($guardian_pcsDetails);

        }else{
             $guardian_pcsDetails['citycode'] = '';
             $guardian_pcsDetails['statecode'] = '';
        }



        $nomineeflag = 'N';
        $is_minor = 'N';
        if(isset($nomineeDetails['nominee_exists']) && $nomineeDetails['nominee_exists'] == 'yes')
        {
            $nomineeflag = 'Y';
            $years = \Carbon\Carbon::parse($nomineeDetails['nominee_dob'])->age;
            if($years < 18){
                $is_minor = 'Y';
            }else{
                $nomineeDetails['guardian_name'] = '';
                $nomineeDetails['guardian_address_line1'] = '';
                $nomineeDetails['guardian_address_line2'] = '';
                $guardian_pcsDetails['citycode'] = '';
                $guardian_pcsDetails['statecode'] = '';
                $nomineeDetails['guardian_pincode'] = '';       
            }
        }else{
            $nomineeDetails['nominee_name'] = '';
            $nomineeDetails['nominee_address_line1'] = '';
            $nomineeDetails['nominee_address_line2'] = '';
            $nominee_pcsDetails['citycode'] = '';
            $nominee_pcsDetails['statecode'] = '';
            $nomineeDetails['nominee_pincode'] = '';
            $is_minor = '';
            $nomineeDetails['nominee_dob'] = '';
            $nomineeDetails['guardian_name'] = '';
            $nomineeDetails['guardian_address_line1'] = '';
            $nomineeDetails['guardian_address_line2'] = '';
            $guardian_pcsDetails['citycode'] = '';
            $guardian_pcsDetails['statecode'] = '';
            $nomineeDetails['guardian_pincode'] = '';
        }

        if($customerOvdData->auto_renew == 'Y'){

            $autoRenew = 'U'; //Unlimited
            $autoClosure = 'N'; // autoClosure No if auto renew is yes

            if($customerOvdData->maturity == 1){

              $renewalOption = 'M';
            }else{

              $renewalOption = 'P';
            }

        }else{

            $autoRenew = 'N';
            $autoClosure = 'Y';
            $renewalOption = '';
        }

        $todayDate =  Carbon::now()->format('d-m-Y 00:00:00');
        //$accountOpDate = Carbon::now()->format('Y-m-d');

        if(env('APP_SETUP') == 'PRODUCTION'){
		   if(isset($fundingDetails['funding_date']) && $fundingDetails['funding_date'] != ''){
			    $accountOpDate = Carbon::parse($fundingDetails['funding_date'])->format('Y-m-d');
		   }else{
				$accountOpDate = Carbon::now()->format('Y-m-d');
		   }
        }else{

           $accountOpDate = Carbon::create(2021, 2, 22)->format('Y-m-d');
        }

        $tenure_months = ($customerOvdData->years * 12) + $customerOvdData->months;
        $tenure_days = $customerOvdData->days;


        // $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('neft_account_number');
        // if($customerOvdData['initial_funding_type'] == 1)
        // {
        //     $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('cheque_account_number');
        // }


        switch ($customerOvdData->initial_funding_type) {
            case '1':
            //For CHEQUE
                $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('cheque_account_number');
                break;

            case '2':
             //For NEFT
                $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('neft_account_number');
                break;

             case '3':
               //For DCB AC
                $bankAccountNumber = $customerOvdData->account_number;
                break;

            case '4':
            //For 3rd Party
                return json_encode(['status'=>'fail','msg'=>'Funding Type could not be identified. Please contact CUBE Admin ','data'=>[]]);
                break;

            case '5':
            //For Others
                return json_encode(['status'=>'fail','msg'=>'Unauthorized attempted. Please contact CUBE Admin','data'=>[]]);
                break;

            default:
                return json_encode(['status'=>'fail','msg'=>'Funding Type could not be identified. Please contact CUBE Admin ','data'=>[]]);
                break;
        }
        
        if($key == 0){
            $rel_type = 'M';
        }else{
            $rel_type = 'J';
        }        
        
        $tdDetailsArray = ['BATCH_NUM' => $batch_no,
                            'CUST_ID' => $customerOvdData->customer_id,
                            'TEMP_ACCT' => '',
                            'FORACID' => '',
                            'SCHM_CODE' => $chemeCodeDetails['scheme_code'],
                            'MODE_OF_OPERATION' => $modDetails['code'],
                            'PASSBOOK_STMT' => 'R',
                            'DEPOSIT_AMOUNT' => $customerOvdData->td_amount,
                            'DEPOSIT_PERIOD_MTH' => $tenure_months,
                            'DEPOSIT_PERIOD_DAYS' => $tenure_days,
                            'AUTO_RENEWAL_FLG' => $autoRenew,
                            'FREE_CODE_7' => $empHRMSnumber,
                            'SOL_ID' => $accountDetails['branch_id'],
                            'NOMINEE_FLG' => $nomineeflag,
                            'NOMINEE_NAME' => $nomineeDetails['nominee_name'],
                            'NOM_RELATION' => $relationCode,
                            'NOM_ADDR1' => $nomineeDetails['nominee_address_line1'],
                            'NOM_ADDR2' => $nomineeDetails['nominee_address_line2'],
                            'NOM_CITY_CODE' => $nominee_pcsDetails['citycode'],
                            'NOM_STATE_CODE' => $nominee_pcsDetails['statecode'],
                            'NOM_CNTRY_CODE' => $countryCode,
                            'NOM_PIN_CODE' => $nomineeDetails['nominee_pincode'],
                            'NOM_MINOR_FLG' => $is_minor,
                            'NOM_DOB' => $nomineeDetails['nominee_dob'],
                            'NOM_GUARD_CODE' => '',
                            'NOM_GUARD_NAME' => $nomineeDetails['guardian_name'],
                            'NOM_GUARD_ADDR_1' => $nomineeDetails['guardian_address_line1'],
                            'NOM_GUARD_ADDR_2' => $nomineeDetails['guardian_address_line2'],
                            'NOM_GUARD_CITY' => $guardian_pcsDetails['citycode'],
                            'NOM_GUARD_STATE' => $guardian_pcsDetails['statecode'],
                            'NOM_GUARD_COUNTRY' => $guardiancountryCode,
                            'NOM_GUARD_POSTAL' => $nomineeDetails['guardian_pincode'],
                            'ACCT_OPN_DATE' => $accountOpDate,
                            'REMARKS_FLD' => '',
                            'AOF_NUMBER' => $accountDetails['aof_number'],
                            'ACCT_LABEL' => '',
                            //'REL_TYPE' => 'A',
                            'REL_TYPE' => $rel_type,
                            'REL_CODE' => '000',
                            //'JOINT_ACCT_CODE' => '001',
                            'JOINT_ACCT_CODE' => str_pad($key+1,3,0,STR_PAD_LEFT),
                            //'OPERATIVE_ACCOUNT' => $bankAccountNumber,
							'OPERATIVE_ACCOUNT' => $operativeAccount,		// As per input from Mridul
                            'SI_CLASS' => '',
                            'SI_EXEC_TIME' => '',
                            'NEXT_EXEC_DATE' => '',
                            'CARRY_FORWARD' => null,
                            'CARRY_FORWARD_TIME' => '',
                            'SI_CHRG_ACCP_EVENT' => '',
                            'SI_CHRG_EXEC_EVENT' => '',
                            'MEMO_PAD' => 'N',
                            'FIXED_AMOUNT' => null,
                            'DR_ACCT_NUM' => $bankAccountNumber,
                            'CR_ACCT_NUM' => null,
                            'CHRG_RATE' => null,
                            'TEXT' => null,
                            'PRE_OPN_FLG' => 'F',
                            'OPN_COMP_FLG' => 'N',
                            'UPL_USER_ID' => 'CUBE',
                            'UPL_TIME' => $todayDate,
                            'UPLOAD_SOL' => '900',
                            'LCHG_USER_ID' => 'CUBE',
                            'LCHG_TIME' => $todayDate,
                            'REMARKS' => $customerOvdData->initial_funding_type == 3 ? '005' : '006',  //If funding thru dcb (3)
                            'OPN_TYPE_FLG' => 'A',
                            'SI_SRL_NUM' => '',
                            'TRAN_ID' => '',
                            'AUTO_CLOSURE_FLG' => $autoClosure,
                            'RENEWAL_OPTION' => $renewalOption,
                            'TD_TYPE' => config('constants.INTREST_PAYOUT_VALUES.'.$customerOvdData->interest_payout),
							
							'AUTO_CHQ_REQ' => $nomineeflag,  // Print Receipt, NomineeName - Mail 130422..Changes After Manikandan Request

							/* Additional Fields for Cr of TD proceeds and benif details */
							'PYMT_TYPE' => 'N',
							'BEN_ACCT' => $benefAccount, 						//$customerOvdData->account_number,
							'IFSC_CODE' => $benefIfsc,							//$AccountName$customerOvdData->ifsc_code,
							'ACCT_TYPE' => 'SA',
							'BEN_NAME' => $benefAccountName, 					//$customerOvdData->account_name,
							'CHRG_FLG' => 'Y',
							'BEN_ADDR_1' => $customerOvdData->current_address_line1,
							'ENTITY_CRE_FLG' => 'Y',
							'DEL_FLG' => 'N'

                        ];
            $finacle_table = env('FINACLE_TABLE');
            $finacleQueryInstance = DB::connection('oracle2')->table($finacle_table);
            $id = $finacleQueryInstance->insert($tdDetailsArray);
            DB::disconnect('oracle2');
        }
        }

        //echo "<pre>";print_r($tdDetailsArray);exit();
        //$saveTDAccountDetails = DB::table('TD_ACCOUNT_DETAILS')->insert($tdDetailsArray);
        return $id;
    }

    public static function getZipDetailsByZipCode($zipcode, $countryId)
    {
        $country_code = DB::table('COUNTRIES')->select('country_code')
                                                ->where('ID',$countryId)
                                                ->pluck('country_code')->toArray();
        $countryCode = current($country_code);


        $finpcsDetails = DB::table('FIN_PCS_DESC')->where('pincode',$zipcode)
                                                ->where('countrycode', $countryCode)
                                                ->get()->toArray();

        if(count($finpcsDetails) == 0){
           $finpcsDetails['citycode'] = '';
           $finpcsDetails['statecode'] = '';
        }else{
           $finpcsDetails = (array) current($finpcsDetails);
        }

        // echo "<pre>";print_r($countryId);exit;
        if (!isset($finpcsDetails['countrycode']) || $finpcsDetails['countrycode'] == '') {
            $finpcsDetails['countrycode'] = 'IN';
        }
        return $finpcsDetails;
    }
    // public static function getStateCodeByZipCode($zipcode)
    // {
    //     $stateCode = DB::table('FIN_PCS_DESC')->where('pincode',$zipcode)
    //                                         ->pluck('statecode')->toArray();

    //     $stateCode = current($stateCode);
    //     return $stateCode;
    // }

    public static function clearSession()
    {
        Session::forget('UserDetails');
        Session::forget('formId');
        Session::forget('nomineeIds');
        if(Session::get('is_review') == 1){
            Session::put('is_review',0);
        }
        if(Session::get('in_progress') == 1){
            Session::put('in_progress',0);
        }
    }

	public static function getRandomValue($maxLength)
    {
        $min = (int)pow(10, $maxLength - 1);
        $max = (int)pow(10, $maxLength) - 1;
        // echo "<pre>";print_r(str_pad(mt_rand($min, $max),$maxLength,'0',STR_PAD_LEFT));exit;
		return str_pad(mt_rand($min, $max),$maxLength,'0',STR_PAD_LEFT);
	}

    public static function accountCheck($formId)
    {
       //Check fro MOP
       $mopDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                 ->whereNull('MODE_OF_OPERATION')
                                                  ->update(['MODE_OF_OPERATION'=>1]);
        return true;
    }

    public static function exportpdf($aofNumber)
    {
        // echo "<pre>";print_r($aofNumber);exit;
        $customer_type = '';
        $formDetails = DB::table('ACCOUNT_DETAILS')
            ->select('ACCOUNT_DETAILS.ID','USERS.EMPLDAPUSERID','ACCOUNT_DETAILS.CREATED_AT')
            ->leftjoin('USERS','USERS.ID','ACCOUNT_DETAILS.CREATED_BY')
            ->where('ACCOUNT_DETAILS.AOF_NUMBER',$aofNumber)
            ->get()->toArray();

        $formDetails = (array) current($formDetails);
        $formId = $formDetails['id'];

        //get form details
        $formDetailsArray = CommonFunctions::getFormDetails($formId);
        if(isset($formDetailsArray['userDetails'][0]['customerOvdDetails']->dob)){

            $pdfPass = $formDetailsArray['userDetails'][0]['customerOvdDetails']->dob;
            $pdfPass = Carbon::parse($pdfPass)->format('dmY');
        }else{

            $pdfPass = 'DCBCUBEAIS';
        }
        //echo "<pre>";print_r($timestamp);exit;
        $accountType = $formDetailsArray['accountDetails']['account_type_id'];
        if($formDetailsArray['accountDetails']['is_new_customer'] == 0)
        {
            $customer_type = "ETB";
        }
        $no_of_account_holders = $formDetailsArray['accountDetails']['no_of_account_holders'];
        $cifDeclarationDetails = DB::table('SUBMISSION_DECLARATION_FIELDS')->where('FORM_ID',$formId)->get()->toArray();
        $cifDeclarationDetails = current($cifDeclarationDetails);

        $enc_fields = [1,2,3,6,4];
        $huf_cop_row=[];
            if($formDetailsArray['accountDetails']["constitution"]=="NON_IND_HUF"){
                $huf_cop_row = DB::table("NON_IND_HUF as HUF")->select("HUF.*","REL.DISPLAY_DESCRIPTION as relation")
                ->leftJoin("RELATIONSHIP as REL","REL.ID","=","HUF.HUF_RELATION")
                ->where("HUF.FORM_ID",$formId)->where("HUF.DELETE_FLG","N")
                ->get()->toArray();
                $huf_cop_row = (array) $huf_cop_row;
            }

             //GROSS INCOME CHANGE LOGIC
             for($seq=0;count($formDetailsArray['userDetails'])>$seq;$seq++){

                $grossincomeId = $formDetailsArray['userDetails'][$seq]['riskDetails']->gross_income;

                $getdescgrossIncome = DB::table('GROSS_INCOME')->select('GROSS_ANNUAL_INCOME')
                                                               ->whereId($grossincomeId)
                                                               ->get()->toArray();
                if(count($getdescgrossIncome)>0){
                    $getdescgrossIncome =  (array) current($getdescgrossIncome);
                    $formDetailsArray['userDetails'][$seq]['riskDetails']->gross_income = $getdescgrossIncome['gross_annual_income'];
                }
            }

        view()->share(['accountDetails' => $formDetailsArray['accountDetails'],
            'is_aof_tracker'=>true,
            'no_of_account_holders'=>$no_of_account_holders,
            'customer_type'=>$customer_type,
            'huf_cop_row'=>$huf_cop_row,
            'formId'=>$formId,
            'accountType'=>$accountType,
            'enc_fields' =>$enc_fields,
            'userDetails'=>$formDetailsArray['userDetails'],
            'files'=>$formDetailsArray['files'],
            'declarationsList'=>$formDetailsArray['declarationsList'],
            'username'=>$formDetails['empldapuserid'],
            'nomineeDetails'=>$formDetailsArray['nomineeDetails'],
            'cifDeclarationDetails'=>$cifDeclarationDetails]);
        $pdf = PDF::loadView('bank.submissionform');
        //return view('bank.submissionform');
        $pdf->setEncryption($pdfPass);
        
        $filePath = base_path('/conf_data/email_aof/DCB-CUBE-'.$aofNumber.'.pdf');
        if(file_exists($filePath)){
            unlink($filePath);
        }
        return $pdf->save($filePath);
    }



       public static function inputRegexValidation($inputstring,$type){


          $response = preg_replace(config('constants.REGEX.'.$type), '', $inputstring);
           return $response;


    }


    public static function getMessages($activitycode){


        $messages = array();
        $messages = DB::table('MESSAGES')->where('is_active',1)
                                         ->where('ACTIVITY_CODE',$activitycode)
                                          ->get(['id','message_type','message','subject','function_name'])
                                          ->toArray();
        return $messages;
    }


    public static function processCustomerNotification($formId,$activitycode,$applicant=1){

        $sendNotificationTo = 'ALL';

        $messages = Self::getMessages($activitycode);

        $aofNumber = DB::table('ACCOUNT_DETAILS')->whereId($formId)->pluck('aof_number')->toArray();
        $aofNumber = current($aofNumber);

        $ovddetails = DB::table('CUSTOMER_OVD_DETAILS')
                                                      ->select('ID','MOBILE_NUMBER','EMAIL','FIRST_NAME','LAST_NAME','MIDDLE_NAME','APPLICANT_SEQUENCE','IS_NEW_CUSTOMER')
                                                      ->where('form_id',$formId)
                                                      ->where('applicant_sequence',$applicant)
                                                      ->get()->toArray();


       $accountDetails = DB::table('ACCOUNT_DETAILS')
                                            ->where('id',$formId)
                                            ->get()->toArray();
       $accountDetails = current($accountDetails);

        if((count($messages) != 1) || (count($ovddetails) < 1)){
            return false;
        }

        for ($i=0; $i < count($ovddetails) ; $i++)
        {
            if(($sendNotificationTo != 'ALL') && ($i > 0)){
                break;
            }

            $messagesdetails = (array) $messages;
            $messagesdetails = current($messagesdetails);
            $text = $messagesdetails->message;

            $words = explode(' ', $messagesdetails->message);
                $params = array();
                foreach ($words as $word) {
                    if((strstr($word,"{{")) && (strstr($word,"}}")))
                    {
                        $p1 = strpos($word,"{{");
                        $p2 = strpos($word,"}}");
                        if($p2 > $p1){

                          $word1 = substr($word,$p1+2,strpos($word,"}",0)-($p1+2));
                          array_push($params, $word1);
                        }
                    }
                }

            $user = array();
            //$text = '';
            $paramDetails = DB::select(" SELECT ".$messagesdetails->function_name."(".$formId.",".$ovddetails[$i]->applicant_sequence.") FROM DUAL");
            $paramDetails = (array) current($paramDetails);
            $temp = array_values($paramDetails);
            $currValues = json_decode($temp[0]);

            for ($p=0; $p < count($params) ; $p++) {
                 $index = 'r'.($p+1);
                 $text = str_replace('{{'.$params[$p].'}}', $currValues->$index, $text);
            }

            if($messagesdetails->message_type == 'email'){

                  $exportPDF = CommonFunctions::exportPDF($aofNumber);
                  $filename = 'DCB-CUBE-'.$aofNumber.'.pdf';
                  $filePath = '/conf_data/email_aof/'.$filename;

            }else{
                $filePath = '';
            }
            $currentDBtime = self::getCurrentDBtime();


            $email_sms_data[] = array('activity_code' => $activitycode,
                                                    'message_id' => $messagesdetails->id,
                                                    'message_type' => $messagesdetails->message_type,
                                                    'message_content' => $text,
                                                    'parameters' => '',
                                                    'email_subject' => $messagesdetails->subject,
                                                    'when' => 'NOW',
                                                    'sent_by' => 1,
                                                    'sent_to' => '',
                                                    'email_id' => $ovddetails[$i]->email,
                                                    'mobile' => $ovddetails[$i]->mobile_number,
                                                    'sent_date' => $currentDBtime,
                                                    'created_at' => $currentDBtime,
                                                    'attachment' => $filePath,
                                                    'aof_number' => $aofNumber,
                                                );


        }

        $result = '';
        if(count($email_sms_data) > 0){
            $activities = DB::table('EMAIL_SMS_MESSAGES')->insert($email_sms_data);
        }

        if($result){
            return true;
        }else{
            return false;
        }
    }


    public static function getdynatextfordeclaration($blade_id,$declarationExtraInfo)
    {
        $dynaText = '';
        switch ($blade_id) {
            case 'name_mismatch':
                  $dynaText = 'Name as per PAN '.$declarationExtraInfo['nameOnPan'].' does not match with Applicant name '.$declarationExtraInfo['username'];
                break;

            default:

                break;
        }
        return  $dynaText;
    }

    public static function getdynatextforNpcdeclaration($declaration_type,$npcdeclarationExtraInfo)
    {

        $npcdynaText = '';
        switch ($declaration_type) {
            case 'name_mismatch':
                  $nop = $npcdeclarationExtraInfo->nameOnPan;
                  $uname = $npcdeclarationExtraInfo->username;

                  if(($nop == null) || ($nop == false)){
                    $nop = '';
                  }
                  if(($uname == null) || ($uname == false)){
                    $uname = '';
                  }
                  $npcdynaText = 'Name as per PAN '.$nop.' does not match with Applicant name '.$uname;
                break;

            default:

                break;
        }
        return  $npcdynaText;
    }


    public static function moveAttactToMark($formId)
    {

        $current_timestamp = Carbon::now()->format('d-m-Y_His');
        $folder = storage_path('/uploads/attachments/'.$formId);

        if (!File::exists($folder.'/signed')) {
            File::makeDirectory($folder.'/signed', 0775, true, true);
        }
        $accountType = Session::get('accountType');
        $isReview = Session::get('is_review');

        if(file_exists($folder)){
                $filesInFolder = \File::files($folder);
                foreach($filesInFolder as $path) {
                    $file = pathinfo($path);
                    $filename = $file['filename'].'.'.$file['extension'];
                    if($accountType == 2 && $isReview == 1){
                        $checkClearance = DB::table('CLEARANCE')->where('FORM_ID',$formId)->where('CLEARANCE_IMG',$filename)->count();
                        if($checkClearance == 1){
                            continue;
                        }
                    }

                    File::move(storage_path('/uploads/attachments/'.$formId.'/'.$filename), storage_path('/uploads/attachments/'.$formId.'/signed/'.$file['filename'].'_'.$current_timestamp.'.'.$file['extension']));
                }
            }
    }

    public static function getGenLedgerSubHeadCode($formId)
    {
        $GenLedgerSubHeadCode = '14001';

        return $GenLedgerSubHeadCode;
    }

    public static function getDespatchMode($formId)
    {
        $DespatchMode = 'C';

        return $DespatchMode;
    }


    public static function getTDSSlab($customerData,$panType=''){

      $applicantAge = Carbon::parse($customerData['dob'])->age;

      if($customerData['pf_type'] == 'pancard'){
            $panForm  = $panType;
      }else{
           $panForm = 'FORM60';
      }

      $tdsSlab = DB::table('TDS_SLAB')
                            ->select('TDS_CODE','TDS_CODE_FEMALE')
                          ->Where('MIN_AGE', '<=' , $applicantAge)
                          ->Where('MAX_AGE', '>=' , $applicantAge)
                          ->Where('PAN_F60', $panForm)
                          ->get()->toArray();

     $tdsSlab = (array) current($tdsSlab);

        if($customerData['gender'] == "F"){
            $tdsSlab = $tdsSlab['tds_code_female'];
        }else{
            $tdsSlab = $tdsSlab['tds_code'];
        }
      return $tdsSlab;
    }


    public static function getSegment()
    {
        //$segment = array();
        $segment = DB::table('SEGMENT')->select('segment_value','segment_code')->pluck('segment_value','segment_code')->toArray();
        //$segment = current($segment);
        foreach ($segment as $key => $value) {
               $segment[$key] = $value.' - '.$key;
        }

        return $segment;
    }


    public static function checkUniqueUtr($reference, $formId)
    {
        if(preg_match('/[^0-9A-Z]/i',$reference)){
            return ['status'=>'true','msg'=>'Please enter valid UTR number'];
        }

        $FundingUtr = DB::table('FINCON')
			->where('REFERENCE_NUMBER',$reference)
			->where('FORM_ID','!=',$formId)
            ->where('ABORT', null)
			->get()->toArray();
        
        
        $checkpreventUtr = DB::table('UTR_CHECK')->where('UTR_NUMBER',$reference)->count();
        // echo "<pre>";print_r($checkpreventUtr);exit;
        if($checkpreventUtr == 0){
            if (count($FundingUtr) > 0) {
                return ['status' => 'true', 'msg' => 'UTR Number Exists in CUBE'];
            } else {
                return ['status' => 'fail'];
            }
        }else{
            return ['status' => 'true', 'msg' => 'UTR Number Not Valid'];
        }
    }

    public static function updateFundingStatusY($formId, $fundingDate)
    {
        try {
            $clearStatusY = ['FUNDING_STATUS' => 'Y',
                         'FUNDING_DATE'   =>  $fundingDate
                        ];

            $updatecheckClearingStatus = DB::table('FINCON')->where('FORM_ID',$formId)
                                                            ->where('FUNDING_STATUS','N')
                                                            ->update($clearStatusY);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions','updateFundingStatusY',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function updateFtrStatusY($formId,$refNo='',$comments='')
    {
        try {
            $current_timestamp = Carbon::now()->format('Y-m-d H:i:s');

            $FtrStatusY = ['FTR_STATUS' => 'Y',
                             'FTR_DATE'   =>  $current_timestamp,
                             'FTR_REFERENCE_NO'   =>  $refNo,
                              'FTR_BY' => Session::get('userId'),
                              'COMMENTS' => $comments
                            ];

            $updateFtrStatusY = DB::table('FINCON')->where('FORM_ID',$formId)
                                                   ->where('FTR_STATUS','N')
                                                   ->update($FtrStatusY);
            // echo "<pre>";print_r($formId);
            // echo "<pre>";print_r($updateFtrStatusY);
            // DB::commit();

        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


     public static function updateTDFtrStatusY($formId,$comment='TABLE')
    {
        try {
            if($comment == 'TABLE'){
                $comment = 'Via Finacle';
            }
            $current_timestamp = Carbon::now()->format('Y-m-d H:i:s');
            $FtrStatusY = ['FTR_STATUS' => 'Y',
                         'FTR_DATE'   =>  $current_timestamp,
                         'FTR_REFERENCE_NO'   =>  '',
                          'FTR_BY' => '1',
                          'COMMENTS' => $comment
                        ];

            $updateFtrStatusY = DB::table('FINCON')->where('FORM_ID',$formId)
                                               ->where('FTR_STATUS','N')
                                               ->update($FtrStatusY);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions','updateTDFtrStatusY',$eMessage,'',$formId);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function addExceptionLog($Emessage, $request, $catchMsg='')
    {
        try{
			if($request!=''){
				$function = $request->route()->getActionMethod();
				$action = $request->route()->getAction();
				$controller =  strtok(class_basename($action['controller']), '@');
			}else{
				$function = 'Cube Function';

				$controller =  '';
			}

            $ExceptionLog = [
                            'MODULE' => $controller,
                            'FUNCTION_NAME' => $function,
                            'AOF_NUMBER' => '',
                            'FORM_ID' => '',
                            'USER_ID' => Session::get('userId'),
                            'MESSAGE' => $Emessage
                        ];

			$addExceptionLog = DB::table('EXCEPTION_LOG')->insert($ExceptionLog);
			DB::commit();
			return json_encode(['status'=>'fail','msg'=>'Exception logged!','data'=>[]]);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }


    public static function getCoulmnsByTable($table,$is_add_data = false)
    {
        $schema = DB::table('USER_TAB_COLUMNS')->where('table_name',$table)
                                                ->orderBy('column_id')
                                                ->pluck('column_name')->toArray();
        $schema = collect($schema);
        $columns = $schema->flip()->all();
        if($is_add_data)
        {
            $filteredColumns = collect(Arr::except($columns, ['ID','CREATED_BY','CREATED_AT','UPDATED_BY','UPDATED_AT']));
        }else{
            $filteredColumns = collect(Arr::except($columns, ['CREATED_BY','CREATED_AT','UPDATED_BY','UPDATED_AT']));
        }
        $filteredColumns = $filteredColumns->flip()->all();
        if(!$is_add_data){
            array_push($filteredColumns,"ACTION");
        }
        return $filteredColumns;
    }

    public static function getROcoulmnsByTable($table)
    {
        $columnsForRO = DB::table('MASTER_TABLE_CONTROL')->where('table_name',$table)
                                                        ->where('option','RO')
                                                        ->pluck('field_name')->toArray();
        return $columnsForRO;
    }

    public static function getDedupeStatus($formId,$type=''){
		$dd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->orderBy('APPLICANT_SEQUENCE', 'ASC')->get()->toArray();
		// if(count($dd)==0) return [];
		// $ddResponse = (array) null;
        $ddResponse = 'true';
		for($appl=0; $appl < count($dd); $appl++){
            if($dd[$appl]->is_new_customer == 1){
                // $ddResponse[$dd[$appl]->query_id] = $dd[$appl]->dedupe_status;
                if($type == 'GEN_QUERY'){
                    if($dd[$appl]->query_id == ''){
                        $ddResponse = 'false';
		}
                }else{
                    if($dd[$appl]->dedupe_status != 'No Match'){
                        $ddResponse = 'false';
	}
                }
            }
		}
        // echo "<pre>";print_r($ddResponse);exit;
		return $ddResponse;
	}

    public static function getCustIds($formId){
		$ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->orderBy('APPLICANT_SEQUENCE', 'ASC')->get()->toArray();
		if(count($ovd)==0) return [];
		$custIds = (array) null;
		for($appl=0; $appl < count($ovd); $appl++){
			$custIds[$appl] = $ovd[$appl]->customer_id;
		}
		return array($custIds);
	}

     public static function getKycUpdates($formId){
        $ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->orderBy('APPLICANT_SEQUENCE', 'ASC')->get()->toArray();
        if(count($ovd)==0) return [];
        $kycUpdates = (array) null;

        for($appl=0; $appl < count($ovd); $appl++){
            $kycUpdates[$appl] = $ovd[$appl]->kyc_update;
        }
        return array($kycUpdates);
    }

    public static function getInternetBankUpdates($formId){
        $ovd = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->orderBy('APPLICANT_SEQUENCE', 'ASC')->get()->toArray();
        if(count($ovd)==0) return [];
        $internetBank = (array) null;

        for($appl=0; $appl < count($ovd); $appl++){
            $internetBank[$appl] = $ovd[$appl]->internet_banking;

        }
        return array($internetBank);
    }

	public static function getAccountIds($formId){
		$acct = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
		if(count($acct)==0 || count($acct) > 1) return [];
		return array ('SA'=>$acct[0]->account_no, 'TD'=>$acct[0]->td_account_no);
	}

	public static function updateNextRole($formId, $scenario, $fromRole = ''){
		$acct = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
		if(count($acct)==0 || count($acct) > 1) return [];
		switch($scenario){
			case 'L1_hold':
				$nextRole = 3;
				break;
			case 'L1_reject':
				$nextRole = 3;
				break;
			case 'L1_discrepent':
				$nextRole = 2;
				break;
			case 'L1_clear':
				$nextRole = 4;
				break;

			case 'L2_hold':
				$nextRole = 4;
				break;
			case 'L2_reject':
				$nextRole = 4;
				break;
			case 'L2_discrepent':
				$nextRole = 2;
				break;
			case 'L2_clear':
				$nextRole = 1;
				break;

			case 'L3_hold':
				$nextRole = 8;
				break;
			case 'L3_clear':
				if($fromRole!=''){
					$nextRole = $fromRole;
				}else{
					$nextRole = 8;
				}
				break;

			case 'QC_hold':
				$nextRole = 5;
				break;
			case 'QC_discrepent':
				$nextRole = 8;
				break;
			case 'QC_clear':
				$nextRole = 6;
				break;

			case 'AU_hold':
				$nextRole = 6;
				break;
			case 'AU_discrepent':
				$nextRole = 8;
				break;
			case 'AU_clear':
				$nextRole = 9;
				break;

			default:
				$nextRole = Session::get('role');
				break;

		}

		DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['NEXT_ROLE' => $nextRole]);
		DB::commit();
	}

    public static function isFTRDone($formId){

        $acctDetails = DB::table('ACCOUNT_DETAILS')
                                                 ->where('ID',$formId)
                                                 ->where('FUND_TRANSFER_STATUS',1)
                                                 ->get()->toArray();

        $fincon = DB::table('FINCON')
                                 ->where('FORM_ID',$formId)
                                 ->where('FTR_STATUS','Y')
                                 ->get()->toArray();

        if((count($acctDetails) > 0) || (count($fincon) > 0)){
            return  true;
        }else{
            return false;
        }
    }

    public static function isAccountCreated($formId){

        $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();

        $accountType = $accountDetails[0]->account_type;
        $applicationStatus = $accountDetails[0]->application_status;
        if(in_array($applicationStatus,[5,12])){ //form rejected by L1 & L2
            return false;
        }

        $SArequired = false;
        $TDrequired = false;

        // For Savings or ComboSaving - Check if account_no already created
        if(($accountType==1 || $accountType==4) && ($accountDetails[0]->account_no == '' || $accountDetails[0]->account_no == null)){
            $SArequired = true;
        }

        // For TD & Combo  - Check if td_account_no already created
        if(($accountType==3 || $accountType==4) && ($accountDetails[0]->td_account_no == '' || $accountDetails[0]->td_account_no == null)){
            $TDrequired = true;
        }

        if(!$SArequired && !$TDrequired){
            return  true;
        }else{
            return false;
        }
    }

	public static function saveLevel3Updates($formId, $declarationType, $note='', $imageName=''){

		if($note == '' && $imageName == '') return false;

        $declID = DB::table('DECLARATIONS')->where('BLADE_ID', $declarationType)
                            ->pluck('id')->toArray();

		$declID = current($declID);

		$declExists = DB::table('CUSTOMER_DECLARATIONS')
							->where(['FORM_ID' => $formId, 'DECLARATION_TYPE' => $declarationType])
                            ->get()->toArray();

		if(count($declExists) > 0){
		   $scenario = 'Update';
		   if($note == ''){
			   $note = $declExists[0]->dyna_text;
		   }
		   if($imageName == ''){
			   $imageName = $declExists[0]->attachment;
		   }
		   $upsertDetails = Array(
								'FORM_ID' => $formId, 'DECLARATION_TYPE' => $declarationType,
								'ATTACHMENT' => $imageName,
								'DYNA_TEXT' => $note,
								'IS_ACTIVE' => 1,
								'APPLICANT_SEQUENCE' => 1,
								'DECLARATION_ID' => $declID,
								'UPDATED_BY' => Session::get('userId'),
							    'UPDATED_AT' => Carbon::now(),
							);

		   $status = DB::table('CUSTOMER_DECLARATIONS')
							->where(['FORM_ID' => $formId, 'DECLARATION_TYPE' => $declarationType])
							->update($upsertDetails);
		}else{
		   $scenario = 'Insert';
		   $upsertDetails = Array(
								'FORM_ID' => $formId, 'DECLARATION_TYPE' => $declarationType,
								'ATTACHMENT' => $imageName,
								'DYNA_TEXT' => $note,
								'IS_ACTIVE' => 1,
								'APPLICANT_SEQUENCE' => 1,
								'DECLARATION_ID' => $declID,
								'CREATED_BY' => Session::get('userId'),
							    'CREATED_AT' => Carbon::now(),
							);
		   $status = DB::table('CUSTOMER_DECLARATIONS')->insert($upsertDetails);
		}

		if($status){
			return true;
		}else{
			return false;
		}

    }

    public static function getAccountDetailsBasedOnCustID($custId){
        // DB::connection('oracle2')->table('tbaadm.gam')->select('FORACID','SCHM_TYPE','SCHM_CODE','CLR_BAL_AMT')
        // DB::connection('oracle2')->table('tbaadm.gam')->leftjoin('tbaadm.aas','aas.cust_id','gam.cust_id')

        if(env('APP_SETUP') == 'DEV'){
            $finacleTable = DB::table('GAMM')->select('FORACID','SCHM_TYPE','SCHM_CODE','CLR_BAL_AMT')
                                                                        ->where('gamm.CUST_ID',$custId)
                                                                        ->get()->toArray();
        }else{
            $finacleTable = DB::connection('oracle2')->table('tbaadm.gam')->join('tbaadm.aas', 'gam.acid', '=', 'aas.acid')
                                                                        ->select('gam.FORACID','gam.SCHM_TYPE','gam.SCHM_CODE','gam.CLR_BAL_AMT')
                                                                        ->where('aas.CUST_ID',$custId)
                                                                        ->whereIn('gam.schm_type',['SBA','CAA'])
                                                                        ->where('gam.entity_cre_flg','Y')
                                                                        ->where('gam.del_flg','<>','Y')
                                                                        ->where('aas.del_flg','<>','Y')
                                                                        ->where('gam.acct_cls_flg','<>','Y')
                                                                        ->where('gam.schm_code','<>','SB129')
                                                                        ->get()->toArray();

            /*$finacleTable = DB::connection('oracle2')->table('tbaadm.gam')->select('FORACID','SCHM_TYPE','SCHM_CODE','CLR_BAL_AMT')
                                                                        ->where('gam.CUST_ID',$custId)
                                                                        ->whereIn('gam.schm_type',['SBA','CCA','CAA'])
                                                                        ->where('gam.entity_cre_flg','Y')
                                                                        ->where('gam.del_flg','<>','Y')
                                                                       // ->where('aas.del_flg','<>','Y')
                                                                        ->where('gam.acct_cls_flg','<>','Y')
                                                                        ->where('gam.bank_id','01')
                                                                        ->whereNull('gam.acct_cls_date')
                                                                        ->get()->toArray();*/
            
        }

        return $finacleTable;
    }

	public static function ccGrid($custId,$schemeCodeDetails)
    {
			$url = config('constants.APPLICATION_SETTINGS.CUSTOMER_DETAILS_URL');


            $CustIdData = Api::customerdetails($url,'customerID',$custId);

            if(isset($CustIdData['status']) || $CustIdData['status'] == 'Success'){
                
                $CustIdDetails = $CustIdData['data']['customerDetails'];

                if(isset($CustIdDetails['CUST_NRE_FLG']) && $CustIdDetails['CUST_NRE_FLG'] == 'N' && ($schemeCodeDetails['nri_type'] == 'NRE' || $schemeCodeDetails['nri_type'] == 'NRO')){
                    return  Array
                    (
                        'CustomerDetails' => Array
                            (

                             'status' => 'FAILED',
                             'CUST_NRE_FLG' => 'Y',
                             'message' => 'Scheme Validation Failed, Please Check Customer Type'
                            
                            )
                    );
                }

                if(isset($CustIdDetails['CUST_NRE_FLG']) && $CustIdDetails['CUST_NRE_FLG'] == 'Y' && $schemeCodeDetails['ri_nri'] == 'RI'){
                    return  Array
                    (
                        'CustomerDetails' => Array
                            (

                             'status' => 'FAILED',
                             'CUST_NRE_FLG' => 'Y',
                             'message' => 'Scheme Validation Failed, Please Check Customer Type'
                            
                            )
                    );
                }

                $custidage = \Carbon\Carbon::parse($CustIdDetails['DATE_OF_BIRTH'])->age;

                if(($schemeCodeDetails['age'] == 'Senior') && ($custidage < 60)){

                 return  Array
                    (
                        'CustomerDetails' => Array
                            (

                             'status' => 'FAILED',
                             'customerAge' => $custidage,
                             'message' => 'Scheme validation for senior citizen failed'
                            
                            )
                    );
            
                }          
            }

            $getNriSchemecodeList = array();
            if($schemeCodeDetails['nri_type'] == 'NRE'){
                $getNriSchemecodeList = Config('constants.CC_NRE_SCHEME_CODE');
            }
            if($schemeCodeDetails['nri_type'] == 'NRO'){
                $getNriSchemecodeList = Config('constants.CC_NRO_SCHEME_CODE');
            }

			// $custAccounts = Api::accountNumbersWithBalance($custId,1);
            $custAccounts = Self::getAccountDetailsBasedOnCustID($custId);
			$errCondition = false;
            if(count($custAccounts) == 0) {
                $errCondition = true;
                $msg = 'Response array not found';
            } 

            // if(isset($custAccounts['status']) && isset($custAccounts['status']['isSuccess'])) {
            //      if ($custAccounts['status']['isSuccess'] != 'true' || $custAccounts['status']['statusCode'] != 'ER000') {
            //             $errCondition = true;
            //             $msg = 'Negative response';
            //        }     
            // } 
                
            if($errCondition){ 
                return  Array
                (
                    'CustomerDetails' => Array
                        (

                         'status' => 'FAILED',
                         'message' => 'Api Error! '.$msg
                        
                        )
                );
            } 
            

			// if(!isset($custAccounts['response']) || !isset($custAccounts['response']['data']['accounts'])) return 'Error!';

			$accts = $custAccounts;
			$templ = array (
					'acctNo' => '',	'satd' => '', 'active' => '',
					'bal' => 0,	'applicants' => 0, 'minor' => '',
					'age_comment' => '', 'kyc' => '', 'kyc_comment' => '', 'fatca' => '', 'fatca_comment' => '',
					'lienFreeze' => '',	'lienFreeze_comment' => '',	'mop' => '', 'mop_comment' => '',
					'const' => '', 'const_comment' => '', 'nre' => '', 'nre_comment' => '',
					'allowDisallow' => '',	'comments' => '','pancard' => '','mode_of_operation'=>'','rules'=> '','redFields' => []
				);

			$ccgrid = array();

			for($act = 0; $act < count($accts); $act++){

				$t = $templ;
				$t['acctNo'] = $accts[$act]->foracid;
				// $t['satd'] = $accts[$act]->schm_type == 'SBA' ||  $accts[$act]->schm_type == 'CAA' ? 'SA' : 'OTHER';

                switch($accts[$act]->schm_type){
                    case 'SBA':
                        $t['satd'] = 'SA';
                        break;
                    case 'CAA':
                        $t['satd'] = 'CA';
                        break;
                    default:
                        $t['satd'] = 'OTHER';
                        break;
                }

				if($t['satd'] != 'SA' && $t['satd'] != 'CA'){
					$t['allowDisallow'] = 'N';
					$t['comments'] = 'Only savings account permissible';
					array_push($t['redFields'], 'satd');
					array_push($ccgrid, $t);
					continue;
				}

				if($t['satd'] == 'SA' || $t['satd'] == 'CA') {
                    
                    if(!in_array($accts[$act]->schm_code,$getNriSchemecodeList) && $schemeCodeDetails['nri_type'] == 'NRE'){
                        continue;
                    }

                    if(!in_array($accts[$act]->schm_code,$getNriSchemecodeList) && $schemeCodeDetails['nri_type'] == 'NRO'){
                        continue;
                    }
					$saResponse = Api::accountNumberDetails($t['acctNo']);
            // echo "<pre>";print_r($saResponse['accountDetails']);
					if(!isset($saResponse['accountDetails'])){
						$t['allowDisallow'] = 'N'; $t['comments'] = 'Retry';
						array_push($ccgrid, $t);
						continue;
					}

					$t['applicants'] = substr_count($saResponse['accountDetails']['JOINTHOLDERS_CUSTID'], '|') + 1;

					if($t['applicants'] > 2) {
						$t['allowDisallow'] = 'N';
						$t['comments'] = 'Max 2 applicants permissible. ';
						array_push($t['redFields'], 'applicants');
					}

					$t['bal'] = $saResponse['accountDetails']['CLR_BAL_AMT'];
                    if($t['bal'] == 0 || $t['bal'] == '-') {
                        $t['allowDisallow'] = 'N';
                        $t['comments'] .= 'Nil Balance';
                    }
                    // if(($t['bal'] == '.99') && (env('APP_SETUP') != 'PRODUCTION')){

                    //     $t['bal'] = '100000';
                    // }
					$t['active'] = $saResponse['accountDetails']['ACCT_STATUS'] == 'ACTIVE' ? 'Y' : 'N';
					if($t['active']=='N') {
						$t['allowDisallow'] = 'N';
						$t['comments'] .= 'Account not in active state. ';
						array_push($t['redFields'], 'active');
					}

					if(($saResponse['accountDetails']['FREZ_CODE'] == '-' || $saResponse['accountDetails']['FREZ_CODE'] == ' ') && $saResponse['accountDetails']['LIEN_AMT'] == 0){
						$t['lienFreeze'] = 'Ok';
					}else{
						$t['lienFreeze'] = 'Not Allowed';
						$t['lienFreeze_comment'] = 'Freeze: '.$saResponse['accountDetails']['FREZ_CODE'].' Lien: '.$saResponse['accountDetails']['LIEN_AMT'];
						$t['allowDisallow'] = 'N';
						$t['comments'] .= 'Lien marked or account freezed. ';
						array_push($t['redFields'], 'lienfreeze');
					}
//add current account 
					if($saResponse['accountDetails']['MODE_OF_OPERATION'] == 'SELF' || $saResponse['accountDetails']['MODE_OF_OPERATION'] == 'EITHER OR SURVIVOR' || $saResponse['accountDetails']['MODE_OF_OPERATION'] == 'PROPRIETOR'){
						$t['mop'] = 'Ok';
                        $t['mode_of_operation'] = $saResponse['accountDetails']['MODE_OF_OPERATION'];
					}else{
						$t['mop'] = 'Not Allowed';
						$t['mop_comment'] = 'MOP: '.$saResponse['accountDetails']['MODE_OF_OPERATION'];
						$t['allowDisallow'] = 'N';
						$t['comments'] .= 'Only Self or Either or Survivor permitted as MOP. ';
						array_push($t['redFields'], 'mop');
					}

					if($t['allowDisallow'] == 'N'){ // No need to proceed with CustID Api
						array_push($ccgrid, $t);
						continue;
					}

					$firstCustIdData = Api::customerdetails($url,'customerID',$custId);

					if(!isset($firstCustIdData['status']) || $firstCustIdData['status']!='Success'){
						$t['allowDisallow'] = 'N'; $t['comments'] = 'Retry';
						array_push($ccgrid, $t);
						continue;
					}

                    $custDetails = array();

					$firstCustIdDetails = $firstCustIdData['data']['customerDetails'];

                    $custDetails['customer1'] = $firstCustIdDetails;


					   $staff_flag = $firstCustIdDetails['STAFF_FLAG'];
                       $custid_age = \Carbon\Carbon::parse($firstCustIdDetails['DATE_OF_BIRTH'])->age;

                       if(($schemeCodeDetails['age'] == 'Senior') && ($custid_age < 60)){

                            $t['rules'] = 'Not Allowed';
                            $t['rules_comment'] = 'Customer Age :'.$custid_age;
                            $t['allowDisallow'] = 'N';
                            $t['comments'] .= 'Scheme validation for senior citizen failed. ';
                            array_push($t['redFields'], 'rules');
                        }
                        else{
                            $t['rules'] = 'Ok';
                            $t['rules_comment'] = '';

                        }

                        if(($schemeCodeDetails['staff_customer'] == 'Staff') && ($staff_flag == 'N')){

                            $t['rules_comment'] .= ' Staff validation failed.';
                            $t['allowDisallow'] = 'N';
                            $t['comments'] .= 'Staff validation failed. ';
                            if($t['rules'] == 'Ok'){
                              $t['rules'] = 'Not Allowed';
                              array_push($t['redFields'], 'rules');
                            }
                        }



                       $first_Minor = $firstCustIdDetails['CUST_MINOR_FLG'];
					   $first_NRE = $firstCustIdDetails['CUST_NRE_FLG'];
					   $first_ISA_STATUS = $firstCustIdDetails['ISA_STATUS'];
                       $first_PAN_GIR_NUM = $firstCustIdDetails['PAN_GIR_NUM'];

                       if($first_PAN_GIR_NUM == ''){
                            $first_PAN_GIR_NUM = $firstCustIdDetails['PAN2_NUM'];
                       }

					   $first_FATCA_NATIONALITY = $firstCustIdDetails['FATCA_NATIONALITY'];
					   $first_FATCA_CNTRY_OF_RESIDENCE = $firstCustIdDetails['FATCA_CNTRY_OF_RESIDENCE'];
					   //$first_FATCA_PLACEOFBIRTH = $firstCustIdDetails['FATCA_PLACEOFBIRTH'];
					   $first_FATCA_BIRTHCOUNTRY = $firstCustIdDetails['FATCA_BIRTHCOUNTRY'];




						if($first_Minor == 'N'){
							$t['minor'] = 'No';
						}else{
							$t['minor'] = 'Yes';
							$t['minor_comment'] = $firstCustIdDetails['DATE_OF_BIRTH'];
							$t['allowDisallow'] = 'N';
							$t['comments'] .= 'Minor applicant not permitted. ';
							array_push($t['redFields'], 'minor');
						}

						if($first_NRE == 'N' || ($CustIdDetails['CUST_NRE_FLG'] == 'Y' && $schemeCodeDetails['ri_nri'] == 'NRI')){
							$t['nre'] = 'Ok';
						}else{
							$t['nre'] = 'Not Ok';
							$t['nre_comment'] = 'NRE account not permissible';
							$t['allowDisallow'] = 'N';
							$t['comments'] .= 'NRE Customer. ';
							array_push($t['redFields'], 'nre');
						}

						if($firstCustIdDetails['CUST_CONST'] == '001'){
							$t['const'] = 'Ok';
						}else{
							$t['const'] = 'Not Ok';
							$t['const_comment'] = 'CUST_CONST: '.$firstCustIdDetails['CUST_CONST'];
							$t['allowDisallow'] = 'N';
							$t['comments'] .= 'Customer constitution not individual. ';
							array_push($t['redFields'], 'const');
						}

						if($first_ISA_STATUS == 'Y'){
							$t['kyc'] = 'Ok';
						}else{
							$t['kyc'] = 'Not Ok';
							$t['allowDisallow'] = 'N';
							$t['comments'] .= 'KYC not updated. ';
							array_push($t['redFields'], 'kyc');
						}


                        if($first_PAN_GIR_NUM != ''){
                            $t['pancard'] = 'Y';
                        }else{
                            $t['pancard'] = 'N';
                            $t['allowDisallow'] = 'N';
                            $t['comments'] .= 'PANCARD not exists.(First Applicant)';
                            array_push($t['redFields'], 'pancard');
                        }

						if($first_FATCA_NATIONALITY != 'IN' || $first_FATCA_CNTRY_OF_RESIDENCE != 'IN' ||  $first_FATCA_BIRTHCOUNTRY != 'IN'){

                            if($schemeCodeDetails['ri_nri'] == 'NRI' && ($first_FATCA_NATIONALITY != '' && $first_FATCA_CNTRY_OF_RESIDENCE != '' &&  $first_FATCA_BIRTHCOUNTRY != '')){
                                $t['fatca'] = 'Ok';
                           }else{
							$t['fatca'] = 'Not Ok';
							$t['fatca_comment'] = 'Nationality: '.$first_FATCA_NATIONALITY;
								$t['fatca_comment'] .= ', Residency: '.$first_FATCA_CNTRY_OF_RESIDENCE;
								$t['fatca_comment'] .= ', Birth Country: '.$first_FATCA_BIRTHCOUNTRY;
							$t['allowDisallow'] = 'N';
							$t['comments'] .= $t['fatca_comment'].'. ';
							array_push($t['redFields'], 'fatca');
                           }
						}else{
							$t['fatca'] = 'Ok';
						}

					// If there are 2 applicants and everything clear so far..
					if($t['applicants'] == 2 && $t['allowDisallow'] != 'N'){
						$custIdTwo = $saResponse['accountDetails']['JOINTHOLDERS_CUSTID'];
						$custIdTwo = str_replace('|', '', $custIdTwo);
						$secondCustIdData = Api::customerdetails($url,'customerID',$custIdTwo);

						if(!isset($secondCustIdData['status']) || $secondCustIdData['status'] != 'Success'){
							$t['allowDisallow'] = 'R'; $t['comments'] = 'Retry';
							array_push($ccgrid, $t);
							continue;
						}

					   $secondCustIdDetails = $secondCustIdData['data']['customerDetails'];
                       $custDetails['customer2'] = $secondCustIdDetails;


					   $second_Minor = $secondCustIdDetails['CUST_MINOR_FLG'];
					   $second_NRE = $secondCustIdDetails['CUST_NRE_FLG'];
					   $second_ISA_STATUS = $secondCustIdDetails['ISA_STATUS'];
                       $second_PAN_GIR_NUM = $secondCustIdDetails['PAN_GIR_NUM'];

					   $second_FATCA_NATIONALITY = $secondCustIdDetails['FATCA_NATIONALITY'];
					   $second_FATCA_CNTRY_OF_RESIDENCE = $secondCustIdDetails['FATCA_CNTRY_OF_RESIDENCE'];
					   //$second_FATCA_PLACEOFBIRTH = $firstCustIdDetails['FATCA_PLACEOFBIRTH'];
					   $second_FATCA_BIRTHCOUNTRY = $secondCustIdDetails['FATCA_BIRTHCOUNTRY'];

						if($second_Minor == 'N'){
							$t['minor'] = 'No';
						}else{
							$t['minor'] = 'Yes';
							$t['minor_comment'] = '2nd DOB: '.$secondCustIdDetails['DATE_OF_BIRTH'];
							$t['allowDisallow'] = 'N';
							array_push($t['redFields'], 'minor');
						}

						if($second_NRE == 'N' || ($second_NRE == 'Y' && $schemeCodeDetails['ri_nri'] == 'NRI')){
							$t['const'] = 'Ok';
						}else{
							$t['const'] = 'Not Ok';
							$t['const_comment'] = '2nd CUST_CONST: '.$secondCustIdDetails['CUST_CONST'];
							$t['allowDisallow'] = 'N';
							array_push($t['redFields'], 'const');
						}

						if($second_ISA_STATUS == 'Y'){
							$t['kyc'] = 'Ok';
						}else{
							$t['kyc'] = 'Not Ok';
							$t['kyc_comment'] = '2nd Applicant';
							$t['allowDisallow'] = 'N';
							array_push($t['redFields'], 'kyc');
						}


                        if($second_PAN_GIR_NUM != ''){
                            $t['pancard'] = 'Y';
                        }else{
                            $t['pancard'] = 'N';
                            $t['allowDisallow'] = 'N';
                            $t['comments'] .= 'PANCARD not exists.(Second Applicant)';
                            array_push($t['redFields'], 'pancard');
                        }

						if($second_FATCA_NATIONALITY != 'IN' || $second_FATCA_CNTRY_OF_RESIDENCE != 'IN' ||  $second_FATCA_BIRTHCOUNTRY != 'IN'){
                               if($schemeCodeDetails['ri_nri'] == 'NRI' && ($second_FATCA_NATIONALITY != '' && $second_FATCA_CNTRY_OF_RESIDENCE != '' &&  $second_FATCA_BIRTHCOUNTRY != '')){
							        $t['fatca'] = 'Ok';
                               }else{

							$t['fatca'] = 'Not Ok';
							$t['fatca_comment'] = '2nd Applicant Nationality: '.$second_FATCA_NATIONALITY;
								$t['fatca_comment'] .= ', Residency: '.$second_FATCA_CNTRY_OF_RESIDENCE;
								$t['fatca_comment'] .= ', Birth Country: '.$second_FATCA_BIRTHCOUNTRY;
							$t['allowDisallow'] = 'N';
							array_push($t['redFields'], 'fatca');
                               }
						}else{
							$t['fatca'] = 'Ok';
						}

					} //End If to check if there was 2nd CustID

				} // End If -- Check if the account is SA

                if(isset($custDetails['customer1'])){

                   $t['customer1'] = $custDetails['customer1'];

                   if(isset($custDetails['customer2'])){
                      $t['customer2'] = $custDetails['customer2'];
                   }
                }

				array_push($ccgrid, $t);

			} // EndFor
            // echo "<pre>";print_r('-------');exit;

			$sortedGrid = array();
			for($g = 0; $g < count($ccgrid); $g++){
			  if($ccgrid[$g]['satd'] == 'SA' || $ccgrid[$g]['satd'] == 'CA') array_push($sortedGrid, $ccgrid[$g]);
			}
			// for($g = 0; $g < count($ccgrid); $g++){
			//   if($ccgrid[$g]['satd'] == 'TD') array_push($sortedGrid, $ccgrid[$g]);
			// }
             $bal = array_column($sortedGrid, 'bal');
             $ad = array_column($sortedGrid, 'allowDisallow');

             array_multisort($ad, SORT_ASC, $bal, SORT_DESC, $sortedGrid);
             // echo "<pre>";print_r($sortedGrid);exit;

			return $sortedGrid;

	} // End GridCC


    public static function getDashboardUrl($role){
        switch ($role) {
            case '1':
                $url = 'admindashboard';
                break;
            case '2':
                $url = 'bankdashboard';
                break;
            case '7':
                $url = 'inwarddashboard';
                break;
            case '8':
                $url = 'npcdashboard';
                break;
            case '9':
                $url = 'archivaldashboard';
                break;
            case '11':
                $url = 'callcenterdashboard';
                break;
            case '12':
                $url = 'uamdashboard';
                break;
            case '13':
                $url = 'admindashboard';
                break;
            case '15':
                $url = 'makerdashboard';
                break;
            case '16':
                $url = 'delightadmindashboard';
                break;
            case '17':
                $url = 'checkerdashboard';
                break;
            case '18':
                $url = 'oaodashboard';
                break;
			case '19':
                $url = 'amendnpcdashboard';
                break;
             case '20':
                $url = 'amendnpcdashboard';
                break;
            case '21':
                $url = 'amendnpcdashboard';
                break;
            case '22':
                $url = 'amendnpcdashboard';
                break;
            case '23':
                $url = 'amendnpcdashboard';
                break;
            
            default:
                $url = 'npcdashboard';
                break;
        }
        return $url;
    }

    public static function genereateAofNumber($branchId, $amend='N'){

        if($amend == 'N'){
            $sequnceNumber = DB::select('select ACCOUNT_SEQUENCE.nextval from dual'); 

        }else{        
            $sequnceNumber = DB::select('select AMEND_SEQUENCE.nextval from dual');
        }
                    $sequnceNumber = (array) current($sequnceNumber);
                    $sequnceLength = strlen($sequnceNumber['nextval']);
                    if($sequnceLength != 6){
                        $sequnceNumber = str_pad($sequnceNumber['nextval'], 6 , "0", STR_PAD_LEFT);
                    }else{
                        $sequnceNumber = $sequnceNumber['nextval'];
                    }
        $year = substr(Carbon::now()->year, 2);
        //$branchId = Session::get('branchId');
        if($amend == 'N'){
            $aofNumber = $year.$branchId.$sequnceNumber;
        }else{

            $aofNumber = '9'.$year.$branchId.$sequnceNumber;
        }

        return $aofNumber;
    }

    public static function checkDedupeStatus($formId){

        $deDupeClear = true;

        $cust_Type = DB::table('ACCOUNT_DETAILS')->select('IS_NEW_CUSTOMER','ACCOUNT_TYPE','DELIGHT_SCHEME','SOURCE')->where('ID',$formId)->get()->toArray();

        $cust_Type = (array) current($cust_Type);

            $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->get()->toArray();
          
            // $checkNtb = 0;
            // for($i=0;count($customerOvdDetails)>$i;$i++){
            //     if($customerOvdDetails[$i]->is_new_customer == 0){
                  
            //     }else{
            //         $checkNtb++;
            //     }
            // }

            // if($checkNtb == 0){
            //     return true;
            // }
                //if funding type is cheque and it is cleared or not cleared
                if(count($customerOvdDetails) > 0)
                {
                    foreach($customerOvdDetails as $customerOvdData)
                    {
                        $customerOvdData = (array) $customerOvdData;
                        if($customerOvdData['is_new_customer'] == 1){
                            if($customerOvdData['query_id'] == '')
                            {
                                $deDupeStatus = "Dedupe Api has Failed";
                                //if dedupe api is failed. we don't allow to continue
                                $deDupeClear = false;
                                return $deDupeClear;
                            }else{ 
                                switch ($customerOvdData['dedupe_status']) {
                                    //NTB ANY ALL THREE TO CHECK
                                    case 'Match':
                                        $deDupeClear = false;
                                        break;
                                    case 'No Match':
                                        $deDupeClear = true;
                                        break;
                                    case 'Pending':
                                    default:
                                        $dedupeStatusDetails = Api::checklivystatus($customerOvdData['query_id'],$customerOvdData['form_id']);
                                        if (isset($dedupeStatusDetails['response']['data'][0]['decisionFlag'])) {
                                            $deDupeStatus = $dedupeStatusDetails['response']['data'][0]['decisionFlag'];
                                            $deDupeReference = substr($dedupeStatusDetails['response']['data'][0]['remarks'],0,100);
                                            $deDupeReference = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeReference);
                                            $deDupeStatus = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeStatus);
                                        }else{
                                            $deDupeStatus = '';
                                            $deDupeReference = '';
                                        }   
                                         $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdData['id'])->update(['DEDUPE_STATUS'=>$deDupeStatus,'DEDUPE_REFERENCE'=>$deDupeReference]);
                                        if($deDupeStatus == 'No Match'){
                                            $deDupeClear = true;
                                        } else {
                                            $deDupeClear = false;
                                        }
                                    break;
                                }
                            }
                        }else{
                            $deDupeClear = true;
                        }
                                
                        if ($cust_Type['delight_scheme'] == 5) {
                            if($customerOvdData['applicant_sequence'] > 1){
                                if(in_array($customerOvdData['dedupe_status'],['No Match'])){
                                    $deDupeClear = true;
                                }else{
                                    $deDupeClear = false;
                                }
                            }else{
                                if(in_array($customerOvdData['dedupe_status'],['No Match','Match'])){
                                    $deDupeClear = true;
                                }else{
                                    $deDupeClear = false;
                                }
                            }
                        }

                        if($customerOvdData['is_new_customer'] == '0'){

                            if($customerOvdData['query_id'] == '')
                            {
                                $deDupeStatus = "Dedupe Api has Failed";
                                //if dedupe api is failed. we don't allow to continue
                                $deDupeClear = false;
                                return $deDupeClear;
                            }else{
                                //ONLY MATCH ALLOWED
                                if($customerOvdData['dedupe_status'] == '' || $customerOvdData['dedupe_status'] == 'Pending'){
                                    
                                    $dedupeStatusDetails = Api::checklivystatus($customerOvdData['query_id'],$customerOvdData['form_id']);
    
                                    if (isset($dedupeStatusDetails['response']['data'][0]['decisionFlag'])) {
                                        $deDupeStatus = $dedupeStatusDetails['response']['data'][0]['decisionFlag'];
                                        $deDupeReference = substr($dedupeStatusDetails['response']['data'][0]['remarks'],0,100);
                                        $deDupeReference = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeReference);
                                        $deDupeStatus = preg_replace('/[^A-Za-z0-9 \-]/','',$deDupeStatus);
                                    } else{
                                        $deDupeStatus = '';
                                        $deDupeReference = '';
                                    }   
                                    $updateDeDupeStatus = DB::table("CUSTOMER_OVD_DETAILS")->whereId($customerOvdData['id'])->update(['DEDUPE_STATUS'=>$deDupeStatus,'DEDUPE_REFERENCE'=>$deDupeReference]);
                                  
                                    if(in_array($deDupeStatus,['Match'])){
                                        $deDupeClear = true;
                                    }else{
                                        $deDupeClear = false;
                                    }
                                }else{
                                    if(in_array($customerOvdData['dedupe_status'],['Match'])){
                                        $deDupeClear = true;
                                    }else{
                                        $deDupeClear = false;
                                    }
                                }
                            }

                        }

                        if ($deDupeClear == false) {
                      
                            return false;
                        }
                    }
                }
        return $deDupeClear;
    }


    public static function addLogicExceptionLog($controller, $function, $message, $aofNumber='', $formId='')
    {
        try{

            $ExceptionLog = [
                            'MODULE' => $controller,
                            'FUNCTION_NAME' => $function,
                            'AOF_NUMBER' => $aofNumber,
                            'FORM_ID' => $formId,
                            'USER_ID' => Session::get('userId'),
                            'MESSAGE' => $message
                        ];

            $addExceptionLog = DB::table('EXCEPTION_LOG')->insert($ExceptionLog);
            DB::commit();
            return json_encode(['status'=>'fail','msg'=>'Exception logged!','data'=>[]]);
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions','addLogicExceptionLog',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }





    public static function getModeReportAccountsCountByTypeAndStatus($accountType,$status='')
    {

        $userDetails = DB::table('USERS')->where('ID',Session::get('userId'))
                                            ->get()->toArray();
            $userDetails = (array) current($userDetails);
        //fetch applcation count based on account type
        $count = DB::table('ACCOUNT_DETAILS')
                        ->where(['ACCOUNT_TYPE'=>array_keys(config('constants.ACCOUNT_TYPES'),$accountType)]);
        // if($status != ''){
        //     $count = $count->whereIn('APPLICATION_STATUS',$status);
        // }
        
        // if(Session::get('role') == 13)
        // {
        //     $count = $count->whereIn('APPLICATION_STATUS',[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15]);
        // }

        
        $count = $count->count();
        return $count;
    }

    public static function getSchemeCodesDesc($accountType,$schemeCodeId){
    
        $schemeCodesdesc = DB::table('SCHEME_CODES')
                                    ->select('SCHEME_CODE')
                                    ->where('ID',$schemeCodeId)
                                    ->where('ACCOUNT_TYPE',1)
                                    ->get()->toArray();

        if($accountType == 'Current'){
            if($schemeCodeId == 14 || $schemeCodeId == 109){
                $schemeCodeId = 1;
            }
             $schemeCodesdesc = DB::table('CA_SCHEME_CODES')
                                    ->select('SCHEME_CODE')
                                    ->where('ID',$schemeCodeId)
                                    ->get()->toArray();
        }
        $schemeCodesdesc = current($schemeCodesdesc);
       

        return $schemeCodesdesc;
    }



    public static function getTDSchemeCodesDesc($accountType,$schemeCodeId){
    
        $tdschemeCodesdesc = DB::table('TD_SCHEME_CODES')
                                    ->select('SCHEME_CODE')
                                    ->where('ID',$schemeCodeId)
                                    ->where('ACCOUNT_TYPE',3)
                                    ->get()->toArray();
        $tdschemeCodesdesc =  current($tdschemeCodesdesc);

    return $tdschemeCodesdesc;
    }


    public static function getBranchSubmissionDate($formId){
         
         $getsubmissiondate = DB::table('STATUS_LOG')->select('CREATED_AT')
                                                     ->where('FORM_ID',$formId)
                                                     ->where('ROLE',2)
                                                     ->where('STATUS',2)
                                                     ->get()->toArray();
        $getsubmissiondate =  current($getsubmissiondate);
        return  $getsubmissiondate;
    }

    public static function getAofStatus($formId){
        // echo "<pre>";print_r($formId);exit;

        $getAofStatus = DB::table('ACCOUNT_DETAILS')
        ->join('AOF_STATUS','AOF_STATUS.ID','=','ACCOUNT_DETAILS.APPLICATION_STATUS')
        ->select('AOF_STATUS.STATUS')
        ->where('ACCOUNT_DETAILS.ID',$formId)
        ->get()
        ->toArray();

        $getAofStatus = (array) current($getAofStatus);        
        return $getAofStatus;

    } 

    public static function getDiscreptionDetails($formId){
                   
        $getDiscreptionDetails=DB::table('REVIEW_TABLE')
        ->select(DB::raw("REVIEW_TABLE.COLUMN_NAME || ': ' || REVIEW_TABLE.COMMENTS  AS des_details"))
        ->where('FORM_ID',$formId)
        ->orderby('created_at')
        ->get()->toArray();       
        
        $discrepancyString='';
        
        for($i=0; $i<count($getDiscreptionDetails);$i++){
            $strVal= (array) current($getDiscreptionDetails[$i]);
            $strVal=$strVal[0];
            $strVal=str_replace(';','',$strVal);
            $discrepancyString= $discrepancyString .($i+1).'. '. $strVal.'; ';
            
        }       
        return $discrepancyString;
    }

    public static function getSchemeDetails($formId)
    {
        $accountDetailsforScheme = DB::table('ACCOUNT_DETAILS')
                                                 ->whereId($formId)
                                                 ->get()->toArray();
        $accountDetailsforScheme = (array) current($accountDetailsforScheme);
         // echo "<pre>";print_r($accountDetailsforScheme);exit;

        $accountType = $accountDetailsforScheme['account_type'];

        if ($accountType == 1) {
            $selectSchemeCode = $accountDetailsforScheme['scheme_code'];
            $accountSchemeTable = 'SCHEME_CODES';
        }elseif($accountType==2){
            // $entityaccountDetailsforScheme = DB::table('ENTITY_DETAILS')
            //                                      ->where('FORM_ID',$formId)
            //                                      ->get()->toArray();
            // $entityaccountDetailsforScheme = (array) current($entityaccountDetailsforScheme);
            $selectSchemeCode = $accountDetailsforScheme['scheme_code'];
            // if($accountDetailsforScheme['scheme_code'] == '14'){
            //     $selectSchemeCode = '1';
            // }
            $accountSchemeTable = 'CA_SCHEME_CODES';
        }elseif ($accountType == 3) {
            $selectSchemeCode = $accountDetailsforScheme['scheme_code'];
            $accountSchemeTable = 'TD_SCHEME_CODES';
        }elseif($accountType == 4){
            $selectSchemeCode = $accountDetailsforScheme['td_scheme_code'];
            $accountSchemeTable = 'TD_SCHEME_CODES';
        }
        
        $schemeDetails = DB::table($accountSchemeTable)->whereId($selectSchemeCode)->get()->toArray();
        $schemeDetails = current($schemeDetails);
        // echo "<pre>";print_r($schemeDetails);exit;

        return $schemeDetails;
    }

    public static function getCurrentDBtime()
    {
        $currentDBTime = DB::selectOne("select SYSDATE from dual");
        $currentDBTime = Carbon::parse($currentDBTime->sysdate);
        // echo "<pre>";print_r($currentDBTime);exit;
        return $currentDBTime;
    }    

     public static function getScenario($formId, $screen){

        // echo "<pre>";print_r($applicantOvd);print_r($accountDetails);exit;
        $scenario = 'Insert';
        switch ($screen) {
            case 'basic_details':
                $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID',$formId)->get()->toArray();
                if (count($accountDetails) > 0) {
                    $scenario = 'Update';
                }
                break;

            case 'ovd_details':
                $ovdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();

                if (count($ovdDetails) > 0) {
                    $scenario = 'Update';
                }
                break;

            case 'risk_details':
                $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                if (count($riskDetails) > 0) {
                    $scenario = 'Update';
                }
                break;

            case 'initial_funding':
                $financialDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                $financialDetails = current($financialDetails);

                if ($financialDetails->initial_funding_type != '') {
                    $scenario = 'Update';
                }
                break;

            case 'nominee_details':
                $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)->get()->toArray();
                if (count($nomineeDetails) > 0) {
                    $scenario = 'Update';
                }
                break;

            case 'declaration_details':
                $declarationDetails = DB::table('CUSTOMER_DECLARATIONS')->where('FORM_ID',$formId)->get()->toArray();

                $accountDetails = DB::table('ACCOUNT_DETAILS')->where('id',$formId)->get()->toArray();
                $accountDetails = current($accountDetails);
                
                if (count($declarationDetails) > 0) {
                    $scenario = 'Update';
                }

                if($accountDetails->account_type != 3 && $accountDetails->card_type != '') { 
                    $scenario = 'Update';
                }

                break;
            default:
                $scenario = 'Error';
                break;
        }

        return $scenario;
    }    

    public static function getFormTableDetails($formId,$screen)
    {
        switch ($screen) {
            case 'basic_details':
                    //not availabe                
                break;

            case 'ovd_details':
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                    ->where('ACCOUNT_DETAILS.ID',$formId)
                                    ->get()->toArray();
                $userDetails['AccountDetails'] = (array) current($accountDetails);
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->orderBy('applicant_sequence', 'ASC')
                                            ->get()->toArray();
                array_unshift($customerOvdDetails, "phoney");
                unset($customerOvdDetails[0]);
                $userDetails['customerOvdDetails'] = json_decode(json_encode($customerOvdDetails),true);
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->orderBy('applicant_sequence', 'ASC')
                                                        ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                break;

            case 'risk_details':
                $riskDetails = DB::table('RISK_CLASSIFICATION_DETAILS')->where('FORM_ID',$formId)
                                                                   ->get()->toArray();
                array_unshift($riskDetails, "phoney");
                unset($riskDetails[0]);
                $userDetails['RiskDetails'] = json_decode(json_encode($riskDetails),true);
                $AccountIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                        ->orderBy('applicant_sequence', 'ASC')
                                                        ->pluck('id')->toArray();
                array_unshift($AccountIds, "phoney");
                unset($AccountIds[0]);
                $userDetails['AccountIds'] = $AccountIds;
                break;

            case 'initial_funding':
                $account_type = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE')
                                                        ->where('ID',$formId)
                                                        ->get()->toArray();
                $account_type =  (array) current($account_type);
                $accountType = $account_type['account_type'];

                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)->get()->toArray();

                if (isset($customerOvdDetails[0]->relationship) && $customerOvdDetails[0]->relationship != '') {
                    $relationship = DB::table('RELATIONSHIP')->where('IS_CHEQUE_RELATION', 1)
                                                                ->where('code', str_pad($customerOvdDetails[0]->relationship, 3, '0', STR_PAD_LEFT))
                                                                ->get()->toArray();
                    $relationship = current($relationship);
                    $customerOvdDetails[0]->relationship = $relationship->code;
                }
                
                $userDetails['FinancialDetails'] = (array) current($customerOvdDetails);
                if ($accountType != 1 && $userDetails['FinancialDetails']['initial_funding_type'] == 5) {
                   $userDetails['FinancialDetails']['initial_funding_type'] = '';
                }
                break;

            case 'nominee_details':
                $nomineeDetails = DB::table('NOMINEE_DETAILS')->where('FORM_ID',$formId)
                                                                        ->get()->toArray();
                array_unshift($nomineeDetails, "phoney");
                unset($nomineeDetails[0]);
                $userDetails['NomineeDetails'] = json_decode(json_encode($nomineeDetails),true);
                break;

            case 'declaration_details':
                // echo "<pre>";print_r($formId);exit;
                $declarations = array();
                $customerOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')
                                            ->where('CUSTOMER_OVD_DETAILS.FORM_ID',$formId)
                                            ->orderBy('applicant_sequence', 'ASC')
                                            ->get()->toArray();
                $accountDetails = DB::table('ACCOUNT_DETAILS')
                                            ->where('ACCOUNT_DETAILS.ID',$formId)->get()->toArray();
                $userDetails['AccountDetails'] = (array) current($accountDetails);
                $userDetails['customerOvdDetails'] = $customerOvdDetails;
                if($userDetails['AccountDetails']['account_type'] == 1 && $userDetails['AccountDetails']['delight_scheme'] == 5){
                    $delightSavings = true;
                }else{
                    $delightSavings = false;
                }

                if($delightSavings)
                {
                    $delightKitDetails = DB::table('DELIGHT_KIT')->whereId($userDetails['AccountDetails']['delight_kit_id'])
                                                                ->get()->toArray();
                    $userDetails['DelightDetails'] = (array) current($delightKitDetails);
                }

                $declarationDetails = DB::table('CUSTOMER_DECLARATIONS')
                                            ->where('FORM_ID',$formId)->get()->toArray();
                if(count($declarationDetails) > 0)
                {
                    foreach ($declarationDetails as $declaration)
                    {
                        $declaration = (array) $declaration;
                        $declarations[$declaration['declaration_type']] = 1;
                        if($declaration['applicant_sequence'] != '')
                        {
                            $declarations[$declaration['declaration_type'].'-'.$declaration['applicant_sequence'].'_proof'] = $declaration['attachment'];
                        }else{
                            $declarations[$declaration['declaration_type'].'_proof'] = $declaration['attachment'];
                        }
                    }
                }
                $userDetails['Declarations'] = $declarations;
                break;
            default:
                $userDetails = 'Error';
                break;
        }

        return $userDetails;
    }

    public static function getUserDetails($userId)
    {
        //$userId = Session::get('userId');
        $userDetails = DB::table('USERS')->select('hrmsno','rm_code')->whereId($userId)->get()->toArray();
        $userDetails = (array) current($userDetails);
        
        if (isset($userDetails['hrmsno']) && $userDetails['hrmsno'] != '') {
            $getfinhrsmNo = DB::table('FIN_EMP_MAPPING')->where('HRMS_EMP_ID',$userDetails['hrmsno'])->get()->toArray();

            if(!empty($getfinhrsmNo)){
                $getfinhrsmNo = (array) current($getfinhrsmNo);
                $userDetails['hrmsno'] = $getfinhrsmNo['finacle_emp_id'];
            }
        }
        return $userDetails;
    }



    public static function fetchSourceCodeDetails($source_code)
    {
        
        if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){
            $rct_table = 'tbaadm.rct';
        }else{
            $rct_table = 'rct';
        }

        $finacleQueryInstance = DB::connection('oracle2')->table($rct_table);

        $sourceCodeDetails = $finacleQueryInstance->where('REF_CODE', $source_code)
                                                    ->where('REF_REC_TYPE', 'AH')
                                                    ->where('DEL_FLG','!=','Y')->get()->toArray();

        DB::disconnect('oracle2');
        if (count($sourceCodeDetails)) {
            return $sourceCodeDetails;
        }else{
            return false;
        }
    }

    public static function fetchRmCodeDetails($rm_code, $source_code)
    {
        if((env('APP_SETUP') == 'UAT') || (env('APP_SETUP') == 'PRODUCTION')){
            $upr_table = 'tbaadm.upr';
        }else{
            $upr_table = 'upr';
        }

        if ($rm_code == "EOD0009") { //default RM code given by Mani
            return true;
        }

        $finacleQueryInstance = DB::connection('oracle2')->table($upr_table);


        $rmCodeDetails = $finacleQueryInstance->where('USER_ID', $rm_code)
                                            ->where('USER_EMP_ID', $source_code)
                                            ->where('DEL_FLG','!=','Y')
                                            ->get()->toArray();

        DB::disconnect('oracle2');
        if (count($rmCodeDetails)) {
            return $rmCodeDetails;
        }else{
            return false;
        }
    }   

    public static function apastropheValidations($fields, $fieldsToCheck)
    {

        foreach ($fields as $fieldName => $fieldValue) {
            if (in_array($fieldName, $fieldsToCheck) && $fields[$fieldName] != '' && strpos($fields[$fieldName],"'") != '') {
                return $fieldName;
            }
        }
    }

    public static function removeApastrophe($data, $fieldsCheckApastrophe)
    {
        foreach ($fieldsCheckApastrophe as $fieldName) {
            if ($data[$fieldName] != '') {
                $data[$fieldName] = str_replace("'", "", strtoupper($data[$fieldName]));
            }
        }
        return $data;
    }

    //$errorResponse = CommonFunctions::checkServerError($response);
    // if (isset($errorResponse['status']) && $errorResponse['status'] == 'Error') {
    //         return ['status'=>'Error','data'=>$errorResponse['data'],'message'=>$errorResponse['message']];
    // }
    public static function checkApiError($response)
    {
        if (isset($reponse['httpCode']) && $reponse['httpCode'] == '500' && isset($reponse['httpMessage']) && $reponse['httpMessage'] != '' && isset($reponse['moreInformation'])) {
            return json_encode(['status'=>'Error','msg'=>$reponse['httpMessage'],'data'=>[$reponse['moreInformation']]]);

        }
        return false;
    }

    public static function getTitleForEkyc($dob, $gender)
    {
        if ($dob >= 18 && $gender == 'M') {
            $title = 12;
        }elseif ($dob >= 18 && $gender == 'F') {
            $title = 14;
        }elseif ($dob <= 5 && $dob >= 0) {
            $title = 3;
        }elseif ($dob <= 18 && $dob > 5) {
            if($gender == "M"){
            $title = 11;
        }else{
                $title = 14;
            }
        }else{
            $title = -1;
        }
        return $title;
    }

    public static function getEtbNtbStatus($formId, $no_of_account_holders)
    {
        $EtbNtbStatus = [];
        
        for($applicantSeq=1; $applicantSeq <= $no_of_account_holders; $applicantSeq++) {
           
                $ovdExist = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $formId)
                                                        ->where('APPLICANT_SEQUENCE', $applicantSeq)
                                                        ->get()->toArray();
                if (count($ovdExist) > 0) {
                    
                    $custId = current($ovdExist)->customer_id;
                    if($custId == ''){
                        $EtbNtbStatus['customer_type'][$applicantSeq] = 'NTB';
                    }else{
                        $EtbNtbStatus['customer_type'][$applicantSeq] = 'ETB';                   
                    }                                   
                }else{
                     $EtbNtbStatus['customer_type'][$applicantSeq] = 'NTB';
                }
        }

        if ($EtbNtbStatus['customer_type'][1] == 'ETB') {
                $EtbNtbStatus['account_type'] = 'ETB';
        }else{
            $EtbNtbStatus['account_type'] = 'NTB';
        }

        return $EtbNtbStatus;
    }

    public static function markETB_NTB($formId, $EtbNtbStatus)
    {
        // echo "<pre>";print_r($EtbNtbStatus);exit;

        foreach ($EtbNtbStatus['customer_type'] as $applicantSeq => $status) {
            if ($status == 'ETB') {
                $is_new_customer = 0;
            }else{
                $is_new_customer = 1;
            }

            $markOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
                                                                ->where('applicant_sequence',$applicantSeq)
                                                                ->update(['is_new_customer'=>$is_new_customer]);
            if (!$markOvdDetails) {
                return false;
            }
        }

        if ($EtbNtbStatus['account_type'] == 'ETB') {
            $accountType = 0;
        }else{
            $accountType = 1;
        }

        // $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
       
        // if (count($accountDetails) > 0) {

        $markAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                        ->update(['is_new_customer'=>$accountType]);

        if (!$markAccountDetails) {
            return false;
        }
        // }else{
        //     return false;
        // }
        return true;
        // if($status == 'ETB'){
        //     $is_new_customer = 0;
        // }else{
        //    if($status == 'NTB'){
        //         $is_new_customer = 1;
        //     }else{
        //         return false;
        //     }   
        // }

        // $markOvdDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$formId)
        //                                                         ->where('applicant_sequence',$applicantSeq)
        //                                                         ->update(['is_new_customer'=>$is_new_customer]);

        // $EtbNtbStatus = Self::getEtbNtbStatus($formId);

        // if ($EtbNtbStatus['account_type'] == 'ETB') {
        //     $accountType = 0;
        // }else{
        //     $accountType = 1;
        // }

        // $markAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
        //                                                 ->update(['is_new_customer'=>$accountType]);

        // if (!$markAccountDetails) {
        //     return false;
        // }
        // return true;
    }

    public static function preFlightFTR($formId)
    {
        $checkFundingDone = DB::table('FINCON')->where('FORM_ID', $formId)
                                                ->where('FUNDING_STATUS', 'Y')
                                                ->where('ABORT', null)
                                                ->get()->toArray();

        if (count($checkFundingDone) > 0) {
            return true;
        }
        return false;
    }

    public static function preFlightAccountIds($formId)
    {
        $accountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)
                                                            ->get()->toArray();

        $accountDetails = current($accountDetails);

        $currentDetails = DB::table('ENTITY_DETAILS')->where('FORM_ID',$formId)->get()->toArray();

        $currentDetails = current($currentDetails);

        // echo "<pre>";print_r($currentDetails);exit;

        switch ($accountDetails->account_type) {
            case '1':
                if ($accountDetails->account_no != '') {
                    return true;
                }
                break;
            case '2':
                if (isset($currentDetails->entity_account_no) && ($currentDetails->entity_account_no != '')){
                    return true;
                }
                break;
            case '3':
                if ($accountDetails->td_account_no != '') {
                    return true;
                }
                break;
            case '4':
                if ($accountDetails->account_no != '' && $accountDetails->td_account_no != '') {
                    return true;
                }
                break;
            
            default:
                return false;
                break;
        }

        return false;
    }

    public static function checkOvdFields($ovdDetails)
    {
        $requiredFields = ['gender','title','last_name','short_name','per_country','per_pincode','per_state','per_city','current_country','current_pincode','current_state','current_city'];

        //non ETB 'add_proof_card_number','current_landmark','per_landmark','proof_of_address','father_spouse','proof_of_identity'

        $ovdDetailsFields = [];
        foreach ($requiredFields as $value => $field) {
            if (!isset($ovdDetails[$field])) {
                $ovdDetailsFields[0] = $field;
            }else{
                $ovdDetailsFields[$field] = $ovdDetails[$field];
            }
        }

        return $ovdDetailsFields;
    } 

    public static function getFML_Name($fullName)
    {
        $tokens  = explode(' ', $fullName);
        switch (count($tokens)) {
           case '1':
               $FML_Name['first_name'] = '';
               $FML_Name['middle_name'] = '';
               $FML_Name['last_name'] = $tokens[0];
               break;

            case '2':
               $FML_Name['first_name'] = $tokens[0];
               $FML_Name['middle_name'] = '';
               $FML_Name['last_name'] = $tokens[1];
               break;

            case '3':
               $FML_Name['first_name'] = $tokens[0];
               $FML_Name['middle_name'] = $tokens[1];
               $FML_Name['last_name'] = $tokens[2];
               break;

           default:
                if(count($tokens) > 3){
                    $FML_Name['first_name'] = $tokens[0];
                    $FML_Name['middle_name'] = $tokens[1];

                    $lName = '';
                    for ($i=2; $i < count($tokens) ; $i++) {

                        $lName = $lName.' '.$tokens[$i];
                    }

                    $FML_Name['last_name'] = $lName;

                }else{

                    $Error = true;
                }
               break;
       }
       return $FML_Name;
    }

    public static function isFormValidToContinue($formId){
        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('ID', $formId)
                                ->whereIn('APPLICATION_STATUS', ['1','4','5','10','11','12']) //hold , reject, descripant
                                ->get()->toArray();

                                

        if (count($accountDetails) > 0) {
            return false; // form is not ok to continue
        }        

        return true;
    }

    public static function isPanMandatory($account_type,$requestData)
    {
        switch ($account_type){
           case '1':
               $table = 'SCHEME_CODES';
            break;
           
            case '2':
                $table = 'CA_SCHEME_CODES';
            break;

            case '3':
                $table = 'TD_SCHEME_CODES';
            break;

            case '4':
                $table = 'SCHEME_CODES';
            break;

            case '5':
                $table = 'SCHEME_CODES';
            break;
        }

        $scheme_code = $requestData['AccountDetails']['scheme_code'];
        
        $getSchemecode = DB::table($table)->select('PAN_MANDATORY')
                                                    ->where('ID',$scheme_code)
                                                    ->get()->toArray();
        $getSchemecode =current($getSchemecode);

        if($getSchemecode->pan_mandatory == 'Y'){
            return true;
        }

        if($account_type == 4){
            $scheme_code = $requestData['AccountDetails']['td_scheme_code'];
            $getSchemecode = DB::table('TD_SCHEME_CODES')->select('PAN_MANDATORY')
                                                        ->where('ID',$scheme_code)
                                                        ->get()->toArray();
            $getSchemecode =current($getSchemecode);
            if($getSchemecode->pan_mandatory == 'Y'){
                return true;
            }
        }
        return false;
    }

    public static function amendUniqueNumber($branchId){

        //$random8DigitNumber = random_int(10000000, 99999999);
        //$year = substr(Carbon::now()->year,2);
        //$amendUniqueNumber = $year.$branchId.$random8DigitNumber;
        return Self::genereateAofNumber($branchId, 'Y');
    }

    public static function getUserName($empId){

        $getUserName = DB::table('USERS')->select(DB::raw("EMP_FIRST_NAME|| ' ' ||EMP_MIDDLE_NAME|| ' ' ||EMP_LAST_NAME AS emp_name"))
                                         ->where('ID',$empId)
                                         ->get()
                                         ->toArray();
        $getUserName = (array) current($getUserName);
        return $getUserName;
    }

    public static function getekycphoto($ekyc_no){

        $ekycData = array();
        $ekycData['referenceNumber'] = $ekyc_no;
        $ekycData['txnId'] = 'UKC:0002439120200615165105070';
        $ekycData['timeStamp'] = Carbon::now()->format('d/m/Y H:i:s a');
        $eKYCData = Api::ekycDetails($ekycData,'', 1);
        // print_r($eKYCData); exit;
        
        if(isset($eKYCData['status']) && $eKYCData['status'] == 'Error'){
            return json_encode(['status'=>'fail','msg'=>$eKYCData['message'],'data'=>[]]);
        }
        if(isset($eKYCData['data']['response']['userPhoto']['photo'])){
            return 'data: image/jpeg;base64,'.$eKYCData['data']['response']['userPhoto']['photo'];
        }else{
            return -1;
        }
        
    }


    public static function markAmendImage($curryear,$crfId,$imageName,$typeImage,$custReqFormId){

        $curryear_path = $curryear.'/'.$crfId.'/'.$custReqFormId;
        // echo "<pre>";print_r($curryear_path)
        $image = Image::make(storage_path('/uploads/amend'.'/'.$curryear_path.'/'.$imageName));
        $dimension = $image->height()+200;
        $width = $image->width();
        $height = $image->height()+150;

        $image->resize(null,$height,function($constraint){

            $constraint->aspectRatio();

        });

        $image->resizeCanvas(null,$dimension,'top',false,'#FFFFFF');

        $imageHeight = $image->height();
        $imageWidth = $image->width();
    
        //osv done message
        if($typeImage == 'ovd_section'){
            $message = 'OSV done by '.Session::get('username').' on '.Carbon::now()->format('d M Y H:i a');
        }else{
            $message = 'Signature Verified by '.Session::get('username').' on '.Carbon::now()->format('d M Y H:i a');
        }

        $image->text($message,$imageWidth-240,$imageHeight-25,function($font){
            $font->file(public_path('fonts/OpenSans-Light.ttf'));
            $font->size(20);
            $font->color('#8B0000');
            $font->align('center');
            $font->valign('bottom');

        });

        $file_path = storage_path('/uploads/amend'.'/'.$curryear_path);

        if(!File::exists($file_path)){
            File::makeDirectory($file_path,0775,true,true);
        }
        $osvtag = 'OSV_DONE_';
        $imagesaved = $image->save($file_path.'/'.$osvtag.$imageName);

        return $imagesaved;
    }

    public static function getAccountNumberBasedonFunding($fundingType,$accountNumber){
        switch ($fundingType) {
            case '1':
            //For CHEQUE
               return  $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('cheque_account_number');
                break;

            case '2':
             //For NEFT
               return  $bankAccountNumber = CommonFunctions::getapplicationSettingsDetails('neft_account_number');
                break;

             case '3':
               //For DCB AC
               return  $accountNumber;
                break;

            case '4':
            //For 3rd Party
                return json_encode(['status'=>'fail','msg'=>'Funding Type could not be identified. Please contact CUBE Admin ','data'=>[]]);
                break;

            case '5':
            //For Others
                return json_encode(['status'=>'fail','msg'=>'Unauthorized attempted. Please contact CUBE Admin','data'=>[]]);
                break;

            default:
                return json_encode(['status'=>'fail','msg'=>'Funding Type could not be identified. Please contact CUBE Admin ','data'=>[]]);
                break;
        }
    }

    public static function getTitleGenderBase($gender){
        // echo "<pre>";print_r($gender);exit;
        $titles = array();
        $titles = DB::table('TITLE')->where('is_active',1)
                                    ->whereIn('GENDER',['MFTO','MF',null,$gender])
                                    ->pluck('description','id')->toArray();
        // echo "<pre>";print_r($titles);exit;
        return $titles;
    }

    public static function getaadhardocumentForKyc($customerData,$issueDt){

        $proofOfIdentity = DB::table('OVD_TYPES')->where('ID','1')
                                                 ->get()->toArray();
        $proofOfIdentity = current($proofOfIdentity); 
      
        $aadharrefNumber = $customerData['id_proof_aadhaar_ref_number'];
        if($aadharrefNumber == ''){
            $aadharrefNumber =  $customerData['add_proof_aadhaar_ref_number'];
        }

        $identityArray = [ 
            //Below Block for Identification Details
            "IdentificationType" => $proofOfIdentity->identification_type,
            "CountryOfIssue" => "IN",
            "DocCode" => $proofOfIdentity->id_proof_code,
            "IssueDt" => $issueDt,
            "TypeCode" => $proofOfIdentity->doc_type,
            "PlaceOfIssue" => "I005",
            "ReferenceNum" =>$aadharrefNumber,
            // "ReferenceNum" => '',
            // "preferredUniqueId" => "Y",
            "IDIssuedOrganisation" => "",
            ];

        return $identityArray;
    }


    public static function updateDKStatusHistory($kit_id = '', $kit_number = '', $status = '', $role = ''){

        $currentTime = Carbon::now()->format('Y-m-d H:i:s');

        $statusDetails['dkit_id'] = $kit_id;
        $statusDetails['kit_number'] = $kit_number;
        $statusDetails['dkit_status'] = $status;
        $statusDetails['created_by'] = $role;
        $statusDetails['created_at'] = $currentTime;

        switch ($status) {
            case '1':
                $statusDetails['comments'] = 'GENERATED';
                break;
            case '2':
                $statusDetails['comments'] = 'DELIVERABLES DONE';
                break;
            case '3':
                $statusDetails['comments'] = 'DISPATCHED';
                break;
            case '4':
                $statusDetails['comments'] = 'RECEIVED';
                break;
            case '5':
                $statusDetails['comments'] = 'NOT RECEIVED';
                break;
            case '6':
                $statusDetails['comments'] = 'DAMAGED';
                break;
            case '7':
                $statusDetails['comments'] = 'ALLOCATED';
                break;
            case '8':
                $statusDetails['comments'] = 'UTILIZED';
                break;
            case '9':
                $statusDetails['comments'] = 'DESTROYED';
                break;
            case '10':
                $statusDetails['comments'] = 'MISSING';
                break;
            case '11':
                $statusDetails['comments'] = 'UNALLOCATED';
                break;
            case '12':
                $statusDetails['comments'] = 'DAMAGED_PA';
                break;
            case '13':
                $statusDetails['comments'] = 'DESTROYED_PA';
                break;
            case '14':
                $statusDetails['comments'] = 'MISSING_PA';
                break;    
            default:
                $statusDetails['comments'] = '';
                break;
        }

        // echo '<pre>';print_r($statusDetails);exit;
        $statusHistory = DB::table('DKIT_STATUS_HISTORY')->insert($statusDetails);
        DB::commit();
    }

    public static function getGaurdianCode($code){

        if($code == '001'){
            $gaurdianCode = 'F';
        }elseif($code == '002'){
            $gaurdianCode = 'M';
        }else{
            $gaurdianCode = 'O';
        }
        return $gaurdianCode;
    }
    public static function checkSpecialChars($array){        
        
        $stack = array();
        array_push($stack, $array);
    
        while (!empty($stack)) {
            $current = array_pop($stack);
    
            foreach ($current as $value) {
                if (is_array($value)) {
                    array_push($stack, $value);
                } else {
                    if (strpos($value, '<') !== false || strpos($value, '>') !== false || strpos($value, '&') !== false) {                        
                        return 'NOT_OK';
                    }
                }
            }
        }
            
        return 'ALL_OK';

    }
    public static function checkandmaskaadhaar($formId){

        $getOvdData = DB::table('CUSTOMER_OVD_DETAILS')->select('ID_PROOF_IMAGE','ADD_PROOF_IMAGE','APPLICANT_SEQUENCE','PROOF_OF_IDENTITY','PROOF_OF_ADDRESS')
                                                                    ->where('FORM_ID',$formId)
                                                                    ->where(function ($query)   {
                                                                        $query->where('PROOF_OF_IDENTITY','1')
                                                                             ->orWhere('PROOF_OF_ADDRESS','1');
                                                                    })->get()
                                                                    ->toArray();
                    
        for($i=0;count($getOvdData)>$i;$i++){

            if($getOvdData[$i]->proof_of_identity == '1'){
                $getIdProof = explode(',',$getOvdData[$i]->id_proof_image);  
                for($j=0;count($getIdProof)>$j;$j++){
                    $squence = $getOvdData[$i]->applicant_sequence.'1'.$j;
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ovdmaskCallWrapper','MaskOVD',$squence,$getOvdData[$i]->applicant_sequence,Array($formId,$getIdProof[$j],$getOvdData[$i]->applicant_sequence,'ID'),Carbon::now()->addMinutes(2));
                }
            }

            if($getOvdData[$i]->proof_of_address == '1'){
                $getAddProof = explode(',',$getOvdData[$i]->add_proof_image);
                for($k=0;count($getAddProof)>$k;$k++){
                    $squence = $getOvdData[$i]->applicant_sequence.'2'.$k;
                    ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ovdmaskCallWrapper','MaskOVD',$squence,$getOvdData[$i]->applicant_sequence,Array($formId,$getAddProof[$k],$getOvdData[$i]->applicant_sequence,'ADD'),Carbon::now()->addMinutes(2));
                }
            }
            
            ApiCommonFunctions::insertIntoApiQueue($formId,'ApiCommonFunctions','ovdNumbermaskCallWrapper','MaskOVDNo',null,$getOvdData[$i]->applicant_sequence,Array($formId,$getOvdData[$i]->applicant_sequence),Carbon::now()->addMinutes(2));
        }
    }
    public static function precheckUpdateColumn($requestData)
    {
        try {
            // echo "<pre>";print_r($requestData);exit;
            $applicantId = isset($requestData['applicantId']) && $requestData['applicantId'] != ''?$requestData['applicantId']:'';
            if($requestData['table'] == "non_ind_huf"){
                $columnName = $requestData['column']. $requestData['coparcenar_number'] . '-' . $requestData['applicantId'];
            }else{

            if (isset($requestData['account_type']) && $requestData['account_type'] == 2) {
                $columnName = $requestData['column'];
                if (!str_contains($requestData['column'],'entity')) {
                    if($applicantId != ''){
                        $columnName = $requestData['column'] . '-' . $requestData['applicantId'];
                    }
                }
            } else {
                if($applicantId != ''){
                    $columnName = $requestData['column'] . '-' . $requestData['applicantId'];
                }else{
                    $columnName = $requestData['column'];
                }
                if($requestData['table'] == 'entity_details'){
                
                    $columnName = explode('-',$requestData['column'])[0];
                }
            }
            }
            $checkExit = DB::table('REVIEW_TABLE')->where('FORM_ID', $requestData['formId'])
                                                  ->where('STATUS', '0')
                                                  ->where('COLUMN_NAME', $columnName)
                                                  ->count();
            if ($checkExit > 0) {
                return ['status' => 'success', 'msg' => 'Ok to update data.', 'data' => []];
            } else {
                return ['status' => 'fail', 'msg' => 'Unauthorized tampering detected!.', 'data' => []];
            }

        } catch (\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {
                dd($e->getMessage());
            }
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/CommonFunctions', 'encrypt256', $eMessage);
            return json_encode(['status' => 'fail', 'msg' => 'Error! Please try again', 'data' => []]);
        }
    }

    
     public static function copyFile($imageName,$formId,$requestData)
     {        
       
            $imagetype=explode('-',$requestData['name'])[0]; 
            $oldFilePath = storage_path(config('constants.IMAGE_PATH.TEMP_PATH').'/'.$imageName);

            if($imagetype=='pf_type_image' || $imagetype=='customers_photograph')
            {          
            $folder = storage_path('/uploads/markedattachments/'.$formId);
            $filePath = $folder.'/_DONOTSIGN_'.$imageName;
            }
            else
            {
                $folder = storage_path('/uploads/markedattachments/'.$formId);
                $filePath = $folder.'/'.$imageName;
            }
            
            if(file_exists($oldFilePath))
            {            
                if (File::copy($oldFilePath, $filePath))
                {
                    CommonFunctions::updateDBval($imageName,$formId,$requestData);
                }else
                {
                    $is_uploaded = false;
                }
            }
      }
      public static function updateDBval($imageName,$formId,$requestData)
      {   
        // dd($requestData);
        if($requestData['name']=='cheque_image')
        {
            $imagetype='cheque_image';
            $applicant_sequenceId=1;
        }

        else if($requestData['name']=='customers_photograph')
        {
            $imagetype='customers_photograph';
            $applicant_sequenceId=1;

            $fileNameWithExtension = pathinfo($imageName, PATHINFO_BASENAME);              
            DB::table('ACCOUNT_DETAILS')->where('ID', $formId)
                                                   ->update([
                                'CUSTOMERS_PHOTOGRAPH' => $fileNameWithExtension,                               
                            ]);

        }
        
        else if($requestData['name']=='entity_add_proof_image' || $requestData['name']=='entity_add_proof_back_image')
        {
           
            if($requestData['name']=='entity_add_proof_image')
            {
            $fileNameWithExtension = pathinfo($imageName, PATHINFO_BASENAME); 
                    DB::table('ENTITY_DETAILS')->where('FORM_ID', $formId)
                    ->update(['ENTITY_ADD_PROOF_IMAGE' => $fileNameWithExtension,
                     ]);
            }    
            else if($requestData['name']=='entity_add_proof_back_image')
            { 
            $fileNameWithExtension = pathinfo($imageName, PATHINFO_BASENAME);   
            
            DB::table('ENTITY_DETAILS')->where('FORM_ID', $formId)
                    ->update(['entity_add_proof_back_image'=>$fileNameWithExtension,
                     ]);
            }
        }
        else
        {
         $imagetype=explode('-',$requestData['name'])[0]; 
         $imageposition=explode('-',$requestData['image_type'])[0]; 
        
         $applicant_sequenceId= explode('-',$requestData['name'])[1];         

         $fileNameWithExtension = pathinfo($imageName, PATHINFO_BASENAME);   

         $existingimage=DB::table('CUSTOMER_OVD_DETAILS')->select($imagetype,'ADD_PROOF_CARD_NUMBER','APPLICANT_SEQUENCE')->where('form_id',$formId)->where('APPLICANT_SEQUENCE',$applicant_sequenceId)->get()->toArray(); 
        // echo "<pre>";print_r($existingimage);exit;
         $existingimage=(array) current($existingimage);
        
         if(isset($existingimage[$imagetype]) && $existingimage[$imagetype]!= '')
         {     

            if(($imageposition=='id_proof_image_front' ||  $imageposition=='add_proof_image_front')&& ($applicant_sequenceId == $existingimage['applicant_sequence']))
            {   
                if(str_contains($existingimage[$imagetype], ','))
                {                    
                    $existingtableimage=explode(',',$existingimage[$imagetype]);                   
                    $fileNameWithExtension=$fileNameWithExtension.','.$existingtableimage[1];                    
                }
                else{
                    $fileNameWithExtension=$fileNameWithExtension.','.$existingimage[$imagetype];
                }
                
  
            }
            else if(($imageposition=='id_proof_image_back' ||$imageposition=='add_proof_image_back') &&  ($applicant_sequenceId == $existingimage['applicant_sequence'])){
                
                if(str_contains($existingimage[$imagetype], ','))
                {                    
                    $existingtableimage=explode(',',$existingimage[$imagetype]);                   
                    $fileNameWithExtension=$existingtableimage[0].','.$fileNameWithExtension;                    
                }
                else{
                    $fileNameWithExtension=$existingimage[$imagetype].','.$fileNameWithExtension;
                }

                
            }
         }        

         DB::table('CUSTOMER_OVD_DETAILS')
         ->where('FORM_ID', $formId)
         ->where('APPLICANT_SEQUENCE',$applicant_sequenceId)
         ->update([$imagetype => $fileNameWithExtension]);
       
        }

    }

    public static function pregetOVDList($proofType,$formId=''){
        
        $getaccountDetails = DB::table('ACCOUNT_DETAILS')->select('ACCOUNT_TYPE','IS_NEW_CUSTOMER','SCHEME_CODE','FLOW_TYPE')
                                                         ->whereId($formId)
                                                         ->get()
                                                         ->toArray();
            $getSchemeDesc = array();                                   
        if(count($getaccountDetails) > 0){
            $getaccountDetails = (array) current($getaccountDetails);
                $getSchemeDesc = DB::table('SCHEME_CODES')->select('SCHEME_CODE')->whereId($getaccountDetails['scheme_code'])->get()->toArray();
            }
            $schemecodedesc = '';
            if(count($getSchemeDesc)>0){
                $schemecodedesc = (array) current($getSchemeDesc);
                $schemecodedesc = $schemecodedesc['scheme_code'];
            }

            $selectOvdId = array();
            if(count($getaccountDetails) > 0){

                if($getaccountDetails['account_type'] == '1' && $schemecodedesc == 'SB106'){
                    $selectOvdId = ['9'];
                }

                if($getaccountDetails['account_type'] == '1' && $schemecodedesc == 'SB146'){
                    if($proofType == 'ID_PROOF'){
                        $selectOvdId = ['2'];
                    }

                    if($proofType == 'PER_ADDRESS_PROOF'){
                        $proofType = 'ID_PROOF';
                        $selectOvdId = ['2','9'];
                    }
                }
            }
        $ovdTypes = DB::table('OVD_TYPES')->where([$proofType => '1', 'is_active' => 1]);

        if(count($selectOvdId) > 0){
            $ovdTypes = $ovdTypes->whereIn('ID',$selectOvdId);
        }
        $ovdTypes = $ovdTypes->orderBy('SEQUENCE')->pluck('ovd', 'id')->toArray();

        return $ovdTypes;
    }

    public static function getgrossannualIncome(){
        try{
            $getgrossData =  DB::table('GROSS_INCOME')->select('ID','GROSS_ANNUAL_INCOME')    
                                                ->pluck('gross_annual_income','id')->toArray();
            return $getgrossData;
            
        }catch(\Exception $e){
            return $e->getMessage();
        }
        
    }
    public static function checkMaskImages($aof_No,$type,$role)
    {        
        $checkformid=DB::table('ACCOUNT_DETAILS')->select('ID',)->where('AOF_NUMBER',$aof_No)->get()->toArray();
        $checkformid=(array)current($checkformid);

        $reviewstatus = DB::table('ACCOUNT_DETAILS')->whereId($checkformid['id'])       
        ->get()->toArray();
        $reviewstatus = (array) current($reviewstatus);	 
 
 
        $Images=ExceptionController::imagesdirect($aof_No,$type,$role);
       
        foreach ($Images as $key => $value) 
        {
            if (!empty($value['IMAGE']) && !empty($value['PATH'])) 
            {                       
                                     
                foreach ($value['IMAGE'] as $index => $imageName) 
                {
                    $filePath = rtrim($value['PATH'], '/');
                    $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);                           
                    $columns= ['CUSTOMER_OVD_DETAILS-1'=>'PF_TYPE_IMAGE','CUSTOMER_OVD_DETAILS-2'=>'ID_PROOF_IMAGE','CUSTOMER_OVD_DETAILS-3'=>'ADD_PROOF_IMAGE','CUSTOMER_OVD_DETAILS-4'=>'CURRENT_ADD_PROOF_IMAGE','CUSTOMER_OVD_DETAILS-5'=>'CANCEL_CHEQUE_IMAGE','CUSTOMER_OVD_DETAILS-7'=>'CHEQUE_IMAGE','CUSTOMER_DECLARATIONS'=>'ATTACHMENT','ACCOUNT_DETAILS'=>'CUSTOMERS_PHOTOGRAPH','ENTITY_DETAILS'=>'ENTITY_ADD_PROOF_IMAGE'];
                    $columnName ='';  
                    $imagesidecheck='';
                    $ApplicantSeq='';
                    $image_Id = '';
                if($columns !='')
                {
                    foreach ($columns as $key=>$column) 
                    {                            
                        $tablename=explode('-',$key)[0];  
                        $imagetype= DB::table($tablename);
                        if($tablename=='CUSTOMER_DECLARATIONS')
                        {
                            $imagetype=$imagetype->select($column,'APPLICANT_SEQUENCE','DECLARATION_TYPE');

                        }else if($tablename=='ACCOUNT_DETAILS')
                        {
                            $imagetype=$imagetype->select($column);
                        }
                        else if($tablename=='ENTITY_DETAILS')
                        {
                            $imagetype=$imagetype->select($column,'APPLICANT_SEQUENCE');
                        }
                        else
                        {
                            $imagetype=$imagetype->select($column,'APPLICANT_SEQUENCE','PROOF_OF_IDENTITY','PROOF_OF_ADDRESS');
                        } 

                        $originalFilename = $imageName;
                      
                        if(str_contains($originalFilename, "_DONOTSIGN_")){

                         $originalFilename = $imageName;                        
                        $filenameParts = explode('_', $originalFilename);
                         $imagedata= end($filenameParts);

                         $imagetype=$imagetype->where($column,'like','%'.$imagedata.'%')
                                              ->get()
                                              ->toArray(); 
                       
                        }
                        else{
                            $filenameParts = explode('_', $originalFilename);
                            $imageName = end($filenameParts);
                        $imagetype=$imagetype->where($column,'like','%'.$imageName.'%')
                                                ->get()
                                                ->toArray();  
                        }                      
                           
                        if (count($imagetype)>0) 
                        {
                            $getImageName = (array) current($imagetype);
                            if($tablename=='CUSTOMER_DECLARATIONS')
                            {
                                $columnName = isset($getImageName['declaration_type'])&&$getImageName['declaration_type']!=''?$getImageName['declaration_type']:'';
                                $ApplicantSeq= $getImageName['applicant_sequence'];
                                $getImageName=$getImageName['attachment']; 	
                            }
                            else if($tablename=='ACCOUNT_DETAILS')
                            {
                                $columnName = strtolower($column);
                                $ApplicantSeq=1;	 
                                $getImageName=$getImageName[$columnName];
                            }
                            else if($tablename=='ENTITY_DETAILS')
                            {
                                $columnName = strtolower($column);
                                $ApplicantSeq=$getImageName['applicant_sequence'];;	 
                                $getImageName=$getImageName[$columnName];
                            }
                            else
                            {
                                if($column == 'ID_PROOF_IMAGE'){
                                    $image_Id = $getImageName['proof_of_identity'];
                                }   
                                if($column == 'ADD_PROOF_IMAGE'){
                                    $image_Id = $getImageName['proof_of_address'];
                                }
                                $columnName = strtolower($column);
                                $ApplicantSeq= $getImageName['applicant_sequence'];	 
                                $getImageName=$getImageName[$columnName];  
                            }                          
                                            
                            $imageside=explode(',',$getImageName);
                            $imagesidecheck = null;
                            if(isset($imageside[0]) && $imageside[0]!='' && $imageside[0]==$imageName)
                            {
                               $imagesidecheck='front';                                    
                            }

                            if(isset($imageside[1]) && $imageside[1]!='' && $imageside[1]==$imageName)
                            {
                                $imagesidecheck='back';
                            }                                   
                            $UpdateAllImages=[
                                'AOF_NUMBER'=>$aof_No,
                                'FORM_ID'=>$reviewstatus['id'],                          
                                'CREATED_BY'=>$reviewstatus['created_by'],                            
                                'UPDATED_BY'=>$reviewstatus['updated_by'],
                                'PATH'=>$filePath,
                                'RESULT1'=>$imageName, 
                                'IMAGE_TYPE'=>$columnName, 
                                'SIDE'=>$imagesidecheck,  
                                'EXTENSION'=>$imageExtension,
                                'RESULT2'=>$image_Id,
                                'APPLICANT_SEQUENCE'=>$ApplicantSeq            
                                
                            ];                           
                           
                            $aadhaarMaskedLogs = DB::table('ALL_ATTACHMENTS')
                            ->where('FORM_ID', $checkformid['id'])->where('APPLICANT_SEQUENCE', $ApplicantSeq)->where('IMAGE_TYPE', $columnName)
                            ->where('SIDE',$imagesidecheck)
                            ->get()->toArray();
                           
                           
                            if(count($aadhaarMaskedLogs)==0)
                            {
                                $insertaadhaarMaskedLogs = DB::table('ALL_ATTACHMENTS')->insert($UpdateAllImages); 
                            
                            }
                            else
                            {
                            $insertaadhaarMaskedLogs= DB::table('ALL_ATTACHMENTS')->where('FORM_ID',$checkformid['id'])
                              ->where('APPLICANT_SEQUENCE', $ApplicantSeq)->where('IMAGE_TYPE', $columnName)
                              ->where('SIDE',$imagesidecheck)->update($UpdateAllImages);
                                      
                            }
                        }
                    } 
                }                         
                    
                }                        
            }
        }
    }

    public static function horizontalImageView($formId = ''){
        try{
           
            $getCustomerdetails =  DB::table('CUSTOMER_OVD_DETAILS')->select('APPLICANT_SEQUENCE','PROOF_OF_IDENTITY','ID_PROOF_CARD_NUMBER')
                                                                    ->where('FORM_ID',$formId)
                                                                    ->get()
                                                                    ->toArray();
            if(count($getCustomerdetails) >0){
                $imageArray = [];

                for($i=0;count($getCustomerdetails)>$i;$i++){
                    if($getCustomerdetails[$i]->proof_of_identity == 9){

                        $getekycPhoto =  DB::table('EKYC_DETAILS')->select('EKYC_PHOTO')->where('FORM_ID',$formId)
                                                                                        ->where('APPLICANT_SEQUENCE',$getCustomerdetails[$i]->applicant_sequence)
                                                                                        ->where('EKYC_NO',$getCustomerdetails[$i]->id_proof_card_number)
                                                                                        ->orderBy('ID','DESC')
                                                                                        ->get()->toArray();

                        if(count($getekycPhoto)>0){
                            $getekycPhoto = (array) current($getekycPhoto);                      
                            $getekycPhoto = json_decode(base64_decode($getekycPhoto['ekyc_photo']),true);
                            $imageArray[$getCustomerdetails[$i]->applicant_sequence] = $getekycPhoto['ekyc_photo_details']['photo'];
                        }
                    }
                }

            if(!empty($imageArray)){
                $maxheightImg = 0;
                $totalWidthImage = 0;
                $applicantSeq = '';
                $message = '';
                foreach($imageArray as $appSeq => $image){
                    $imagem = Image::make($image);
                 
                    $imgHeight = $imagem->height();
                    $imgwidth = $imagem->width();
    
                 
                    
                    $totalWidthImage += $imgwidth;
                    if($imgHeight > $maxheightImg){
                        $maxheightImg = $imgHeight;
                    }
                    $applicantSeq .=$appSeq;
                }     
            
                $mergedImage = Image::canvas($totalWidthImage, $maxheightImg,'#ffffff');
                $i=0;
                $imgwidth = 0;
                $imgwidthset = 0;
                foreach($imageArray as $appSeq => $image){
                    $imagem = Image::make($image);
                    $imgwidth = $imagem->width();
                    $imgHeight = $imagem->height();
                    $dimension = $imgHeight + 20;
                    $height = $imagem->height()-20;
                    $imagem->resize(null, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $imagem->resizeCanvas($imgwidth, $dimension, 'top', false, '#FFFFFF');
                    $message = 'Applicant '.$appSeq;
                    $imagem->text($message,$imgwidth - 80,$imgHeight-2,function($font){
                        $font->file(public_path('fonts/OpenSans-Light.ttf'));
                        $font->size(16);
                        $font->color('#000000');
                        $font->align('center');
                        $font->valign('bottom');
                    });
                  
                    if($i == 0){
                        $imgwidthset = $imgwidth;
                        $mergedImage->insert($imagem, 'top-left');
                    }else{
                        $mergedImage->insert($imagem, 'top-left',$imgwidthset,0);
                        $imgwidthset +=$imgwidth;
                    }
                    $i++;
                }
    
                $mergedImage->save(storage_path('/uploads/markedattachments/'.$formId.'/ekycphoto_'.$formId.'_'.$applicantSeq.'.jpeg'));
            }
        }

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public static function getuploadekycphoto($formId,$applicantSeq){
        try{    

            $filePath = storage_path('uploads/markedattachments/'.$formId.'/ekycphoto_'.$formId.'_'.$applicantSeq.'.jpeg');

            if(File::exists($filePath)){
                $getImage = file_get_contents($filePath);
                $getImage = base64_encode($getImage);
                return ['status'=>'success','data'=>$getImage];
            }else{
                return ['status'=>'Error','data'=>'', 'message'=>'Signature file not found'];
            }            

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public static function precheckexstingadddata($table,$column,$newvalue){
        try{

            $getExistingdata = DB::table($table)->where($column,$newvalue)->count();
            
            if($getExistingdata>0){
                return ['status'=>'fail','message'=>'Already data present in table','data'=>[]];
            }else{
                return ['status'=>'sucess','message'=>'Ok to process','data'=>[]];
            }

        }catch(\Exception $e){
            $message = $e->getMessage();
            return $message;
        }

    }

    public static function checkbranchActive($branchId){
      
        $branchIsActive=DB::table('BRANCH')->where('BRANCH_ID',$branchId)->get()->toArray();
        if(count($branchIsActive) >0 ){
        $branchIsActive=(array)current($branchIsActive);
    
        if($branchIsActive['is_active'] != '1' ){

            // return json_encode(['status'=>'fail','msg'=>'Selected Branch is not active','data'=>[]]);
            return ['status'=>'fail','message'=>'Selected Branch is not active. Branch is not eligible for indent','data'=>[]];
        }
    }
    }

    public static function ekycaddsmartsplit($ekycaddressdata){

        $validaddressdata = array();
        $address_line_1 = '';
        $address_line_2 = '';
        $landmark = '';
        
        $addressdata = $ekycaddressdata['address'].' '.$ekycaddressdata['house'].' '.$ekycaddressdata['street'].' '.$ekycaddressdata['locality'].' '.$ekycaddressdata['village'].' '.$ekycaddressdata['landmark'];

        $addressdata = preg_replace('!\s+!', ' ', $addressdata);
 
        $validaddressData1 = self::splitaddress($addressdata);

        if($validaddressData1['address_firstchunk'] != ''){
            $address_line_1 = $validaddressData1['address_firstchunk'];
        }else{
            $address_line_1 = '.';
        }

        if($validaddressData1['address_lastchunk'] != ''){
            
            $validaddressData2 = self::splitaddress($validaddressData1['address_lastchunk']);
            $address_line_2 = $validaddressData2['address_firstchunk'];

            if($validaddressData2['address_lastchunk'] != ''){
                $landmark = substr($validaddressData2['address_lastchunk'],0,44);
            }

        }else{
            $address_line_2 = '';
        }

        $validaddressdata['address_line_1'] = $address_line_1;
        $validaddressdata['address_line_2'] = $address_line_2;
        $validaddressdata['landmark'] = $landmark;

        return $validaddressdata;
    }

    public static function splitaddress($address_data){

        $addressdata = array();
        $getstringChunkdata = explode(' ',$address_data);
        $address_firstchunk= '';
        $address_lastchunk = '';
        $checkString = '';

        for($i=0;count($getstringChunkdata)>$i;$i++){
            $checkString .=' '.$getstringChunkdata[$i];
            $validString = trim($checkString);
            if(strlen($validString) <= 44){
                $address_firstchunk = $validString;
            }else{
                $address_lastchunk .=' '.$getstringChunkdata[$i];
                $address_lastchunk = trim($address_lastchunk);
            }
        }

        $addressdata['address_firstchunk'] = $address_firstchunk;
        $addressdata['address_lastchunk'] = $address_lastchunk;

        return $addressdata;
    }

    public static function huf_proof()
    {
        $proofOfHuf = DB::table('OVD_TYPES')->select('ID','OVD')
                                            ->where('HUF_PROOF', 1)
                                            ->where('IS_ACTIVE', 1)
                                            ->get()
                                            ->toArray();

        return $proofOfHuf;
    }

    public static function normal_proof()
    {
        $proofOfNormal = DB::table('OVD_TYPES')->select('ID','OVD')
                                            ->where('PER_ADDRESS_PROOF', 1)
                                            ->where('IS_ACTIVE', 1)
                                            ->get()
                                            ->toArray();

        return $proofOfNormal;
    }

    public static function ETBNTBcheck($requestData){       
        try
        {                        
           
            if(isset($requestData['proof_of_identity'])&& $requestData['proof_of_identity'] !='' ){           
                
             $proofOfIdentity = DB::table('OVD_TYPES')
                                 ->where('ID',$requestData['proof_of_identity'])
                                 ->get()->toArray();        
             $proofOfIdentity=(array)current($proofOfIdentity);       
     
             if(isset($requestData['id_proof_aadhaar_ref_number'])){  // check for e-kyc
                 if($requestData['proof_of_identity'] == 9 && $requestData['id_proof_aadhaar_ref_number'] !=''){
                     $referenceNo=$requestData['id_proof_aadhaar_ref_number'];
                     $proofOfIdentity['id_proof_code']= 'ADHAR';
                 }
                 else{
                     $referenceNo='';
                 }       
             }else{     
                 $validationFailedField = Rules::specialCharValidations($requestData['id_proof_card_number']);           
                 if (isset($validationFailedField) && $validationFailedField != '') {
                     return json_encode(['status'=>'warning','msg'=>$validationFailedField['msg'],'data'=>[]]);
                 }           
                 if($proofOfIdentity['id_proof_code'] == 'ADHAR'){
                     $custId='001189092';                         
                     $aadharNumber=str_replace('-','',$requestData['id_proof_card_number']);  
                     $formId = Session::get('formId');      
                     $AdharRefNumber=Api::aadharValutSvc($custId,$aadharNumber,$formId);  
                   
                     if($AdharRefNumber['status'] == 'Success'){
                         $referenceNo=$AdharRefNumber['data']['response']['referenceKey'];                    
                                     
                     }else{
                         return json_encode(['status'=>'warning','msg'=>'Failed to check Reference Number for applicant-'.$applicantId,'data'=>[$requestData['proofcardNumber']]]);                   
                     }               
     
                 }
                 else{
                     $referenceNo=$requestData['id_proof_card_number'];
                 } 
             }
            
            if(env('APP_SETUP') == 'DEV'){
                $schema = DB::table('CRM_ENTITYDOCUMENT');
            }else{
                $schema = DB::connection('oracle2')->table('CRMUSER.ENTITYDOCUMENT');
            }
            
             $checkcustExist = $schema->where('DOCTYPECODE',$proofOfIdentity['doc_type'])
                            ->where('DOCCODE',$proofOfIdentity['id_proof_code'])
                            ->where('REFERENCENUMBER',$referenceNo)
                            ->take(10)
                            ->pluck('orgkey');
             
             if(count($checkcustExist) > 0)
             {
                 $customerId = $checkcustExist->implode(',');                    
                 return['status'=>'warning','msg'=>'Customer already exists with customer ID-'.$customerId,'data'=>$customerId];         
             }
             else{

                if(isset($requestData['proof_of_address']) && $requestData['proof_of_address'] !='' ){   
                    $checkaddress=Self::addressETBNTB_check($requestData);                    
                    if(isset($checkaddress['status']) && $checkaddress['status'] != 'success'){                       
                        return['status'=>'warning','msg'=>'Customer already exists with customer ID-'.$checkaddress['data'],'data'=>$checkaddress['data']];
                   }                   
                }    

             }
                
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            CommonFunctions::addExceptionLog($eMessage, $request);
            return json_encode(['status'=>'warning','msg'=>'Error! Failed to check customer exist','data'=>[]]);
        }
    }

    public static function addressETBNTB_check($requestData){

    if(isset($requestData['proof_of_address']) && $requestData['proof_of_address'] !='' ){           

            $proofOfIdentity = DB::table('OVD_TYPES')
                            ->where('ID',$requestData['proof_of_address'])
                            ->get()->toArray();        
            $proofOfIdentity=(array)current($proofOfIdentity);    
    
            if(isset($requestData['add_proof_aadhaar_ref_number'])){ // check for e-kyc
                if($requestData['proof_of_address'] == 9 && $requestData['add_proof_aadhaar_ref_number'] !=''){
                    $referenceNo=$requestData['add_proof_aadhaar_ref_number'];
                    $proofOfIdentity['id_proof_code']= 'ADHAR';
                }
                else{
                    $referenceNo='';
                   
                }                
            }else{ 

                $validationFailedField = Rules::specialCharValidations($requestData['add_proof_card_number']);           
                if (isset($validationFailedField) && $validationFailedField != '') {
                    return json_encode(['status'=>'warning','msg'=>$validationFailedField['msg'],'data'=>[]]);
                } 

                if($proofOfIdentity['id_proof_code'] == 'ADHAR'){
                    $custId='001189092';
                    $aadharNumber=str_replace('-','',$requestData['add_proof_card_number']);  
                    $formId = Session::get('formId');  
    
                    $AdharRefNumber=Api::aadharValutSvc($custId,$aadharNumber,$formId);
                                    
                    if($AdharRefNumber['status'] == 'Success'){
                        $referenceNo=$AdharRefNumber['data']['response']['referenceKey'];                    
                    }else{                    
                        return['status'=>'warning','msg'=>'failed to check Reference Number for applicant-'.$applicantId,'data'=>[$requestData['add_proof_card_number']]];                                      
                    } 
                        
                }           
                else{
                    $referenceNo=$requestData['add_proof_card_number'];
                }
            }    

            if(env('APP_SETUP') == 'DEV'){
                $schema = DB::table('CRM_ENTITYDOCUMENT');
            }else{
                $schema = DB::connection('oracle2')->table('CRMUSER.ENTITYDOCUMENT');
            }
    
            $checkcustExist = $schema->where('DOCTYPECODE',$proofOfIdentity['doc_type'])
                            ->where('DOCCODE',$proofOfIdentity['id_proof_code'])
                            ->where('REFERENCENUMBER',$referenceNo)
                            ->take(10)
                            ->pluck('orgkey');
                                        
            if(count($checkcustExist) > 0)
            {
                $customerId = $checkcustExist->implode(',');               
                return['status'=>'warning','msg'=>'Customer is already exists with customer ID-'.$customerId,'data'=>$customerId];
    
            }
        
      }
    }
    public static function getspacehandlingName($name){
        $validname = preg_replace('/\s+/',' ',$name);
        $validname = trim($validname);
        return $validname;
    }
}
?>