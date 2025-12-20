<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScheduleDataController;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use DB;
use Mail;
use PDF;
use Cookie;
use Crypt;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    */
    protected $userId;
    protected $roleId;

    public function __construct()
    {
        if(Cookie::get('token') != ''){
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            $encoded = explode('.',$this->token)[1];
            $userDetails = json_decode(base64_decode($encoded),true);
            $this->userId = $userDetails['user_id'];
            $this->roleId = $userDetails['role_id'];
        }
    }


     public static  function sendEmailNow()
    {
        try {

                  $getemaildetails = DB::table('EMAIL_SMS_MESSAGES')
                                        ->where('IS_SENT', 0)
                                        ->where('WHEN', 'NOW')
                                        ->where('MESSAGE_TYPE','email')
                                        // ->where('EMAIL_ID','!=','')
                                        ->where('EMAIL_ID','!=',null)
                                        ->where('CREATED_AT', '>=', Carbon::now()->subMonths(4))
                                        ->orderby('ID', 'DESC')->take(30)->get()->toArray();

	        foreach ($getemaildetails as $details){   

                    $details = (array) $details;
                    $current_time = Carbon::now()->toDateTimeString();
                    $subject = $details['email_subject'];
                    $to_email_id = $details['email_id'];

	            if(($subject != '') && ($to_email_id != '')){

                    $view = 'email.emailtemplate';
                    $filename = $details['attachment'];
                    $view_data = [ 'to_email_id' => $to_email_id,
                                   'message_content' => nl2br($details['message_content']),
                                    ];
                    $attachmentError = false;

                    if(strlen($filename) > 0){
                            if(file_exists($filename)){
                                try{
                                Mail::send(['html' => $view], ['view_data'=>$view_data], function($message) use ($to_email_id,$subject,$filename) {
                                                                $message->to($to_email_id)
                                                                                ->subject($subject)
                                                                                ->attach($filename,[$filename]);
                                                        });
                                }catch(\Throwable $e){}

                                                        if(count(Mail::failures()) > 0) {
                                                                 $mail_error = true;
                                                        }else{
                                                                 $mail_error = false;
                                                        }
                                           }else{
                                                   $mail_error = true;
                                                   $attachmentError = true;
                                           }
                   }else{
                        try{
                        Mail::send(['html' => $view], ['view_data'=>$view_data], function($message) use ($to_email_id,$subject) {
                        $message->to($to_email_id)
                                ->subject($subject);
                       });
                        }catch(\Throwable $e){}

                      if(count(Mail::failures()) > 0) {
                                                 $mail_error = true;
                                   }else{
                                                 $mail_error = false;
                                        }
                   }

                    if($mail_error) {

                        $mailMsg = 'Email Failed: ';
                        $mailMsg .= ($attachmentError ? " Attachment Error " : " ").$current_time;

                    } else {
                        $mailMsg = "Email Sent: ".$current_time;
                    }

                    $users = DB::table('EMAIL_SMS_MESSAGES')->whereId($details['id'])
                                                                ->update([
                                                                                        'IS_SENT'=>1,
                                                                                        'EMAIL_RESPONSE'=>$mailMsg,
                                                                                        'SENT_DATE'=> Carbon::now()
                                                                                        ]);

                }

                }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog('could not sent mail');
            CommonFunctions::addLogicExceptionLog('NotificationController','sendEmailNow',$eMessage);
            // return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }

    }


	public static function processNotification($formId, $activity, $activityCode='NONE', $new_amend='new',$attachPath = ''){								

                if($activityCode=='NONE'){ // Process all ActivityCodes inside Activity
                        $tasks = DB::table('MESSAGES')->where('is_active',1)
                                                        ->where('ACTIVITY',$activity)
                                                        ->get()->toArray();
                }else{
                        $tasks = DB::table('MESSAGES')->where('is_active',1)
                                                        ->where('ACTIVITY',$activity)
                                                        ->where('ACTIVITY_CODE',$activityCode)
                                                        ->get()->toArray();
                }
		$refernceNo = '';
		if($new_amend == 'new'){

			$details = DB::table('CUSTOMER_OVD_DETAILS')
                                                  ->select('MOBILE_NUMBER','EMAIL','FIRST_NAME','LAST_NAME','MIDDLE_NAME')
                                                  ->where('form_id',$formId)
                                                  ->get()->toArray();

			$details = (array) current($details);

			$aof = DB::table('ACCOUNT_DETAILS')->select('AOF_NUMBER')
												->whereId($formId)
												->get()
												->toArray();
			$aof = (array) current($aof);
			$details['refernceNo'] = $aof['aof_number'];
		}else{

			$details = DB::table('AMEND_MASTER')
							  ->select('MOBILE_NUMBER','EMAIL_ID','CUSTOMER_NAME')
							  ->where('CRF_NUMBER',$formId)
							  ->get()->toArray();

			$details = (array) current($details);
			$details['refernceNo'] = $formId;
			$details['email'] = $details['email_id'];
		}

		$details['attachment'] = $attachPath;

		
		if(count($tasks)==0 || count($details)==0){
                        return false;
                }

                for($m = 0; $m < count($tasks); $m++){
                        $task = (array) $tasks[$m];
//                      Self::errLog($task);
                        if(isset($task['message_type'])){
                                switch($task['message_type']){
                                        case 'email':
						Self::processMessage($formId, $task, $details);
                                                break;
                                        case 'sms':
						Self::processMessage($formId, $task, $details);
                                                break;
                                        default:
                                                return false;
                                                break;
                                        }
                        } // EndIf
                } // EndFor


        }

	public static  function processMessage($formId, $task, $details){
                $message = Self::processTemplate($task['message'], $task['function_name'], $formId);
         
		$email = isset($details['email']) && $details['email'] != '' ? $details['email'] : '';
		$mobile = isset($details['mobile_number']) && $details['mobile_number'] != '' ? $details['mobile_number'] : '';
		$attachment = isset($details['attachment']) && $details['attachment'] != '' ? $details['attachment'] : '';
		$refernceNo = isset($details['refernceNo']) && $details['refernceNo'] != '' ? $details['refernceNo'] : '';


                $current_time = Carbon::now()->toDateTimeString();
        $email_sms_data[] = array('activity_code' => $task['activity_code'],
                                                        'message_id' => $task['id'],
                                                        'message_type' => $task['message_type'],
                                                        'message_content' => $message,
                                                        'parameters' => '',
                                                        'email_subject' => $task['subject'],
                                                        'when' => 'NOW',
                                                        'sent_by' => 1,
							'sent_to' =>   $email,
							'email_id' =>  $email,
							'mobile' => $mobile,
                                                        'sent_date' => '',
                                                        'created_at' => $current_time,
							'attachment' => $attachment,
							'aof_number' => $refernceNo,
                                                );

        $writeToMessage = DB::table('EMAIL_SMS_MESSAGES')->insert($email_sms_data);
                DB::commit();

        }

        public static  function processTemplate($template, $function, $formId){
                $words = explode(' ', $template);
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
                $paramDetails = DB::select(" SELECT ".$function."(".$formId.",1) FROM DUAL");
                $paramDetails = (array) current($paramDetails);
                $temp = array_values($paramDetails);
                $currValues = json_decode($temp[0]);

                $text = $template;
                $p=1;
                $idx='r'.$p;

                for ($p=0; $p < count($params) ; $p++) {
                         $index = 'r'.($p+1);
                         $text = str_replace('{{'.$params[$p].'}}', $currValues->$index, $text);
                }
                return $text;

        }



        public function errLog($data){
                echo "<pre>"; print_r($data); exit;
        }




}
?>
