<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use App\Helpers\CommonFunctions;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Carbon\Carbon;
use DB;
use Mail;
use DateTime;
use Cookie;
use Crypt;

class SmsController extends Controller
{
    public function checkSmsToBeSendNow()
    {
        try{
            $users = 0;
            $smsDetails = DB::table('EMAIL_SMS_MESSAGES')
                               ->where('message_type','sms')
                               ->where('is_sent',0)
                               ->where('when','NOW')
                                ->where('CREATED_AT','>=',Carbon::now()->subDays(3))
                              ->orderBy('ID','DESC')->take(20)->get()->toArray();

            foreach ($smsDetails as $details)
            {
                $details = (array) $details;
                $messageData = ['subject' => '[CAMS] Alert Notification',
                                'mobile' => $details['mobile'],
                                'message_content'=>$details['message_content']];

                $smsResponse = $this->sendSMS($messageData);

                if($smsResponse != ''){
                   $users = DB::table('EMAIL_SMS_MESSAGES')->whereId($details['id'])
                                                                ->update([      'IS_SENT'=>1,
                                                                                        'SMS_RESPONSE_CODE'=>$smsResponse,
                                                                                        'SENT_DATE'=> CommonFunctions::getCurrentDBtime()
                                                                                        ]);
                }
            }

            if($users)
            {
                return json_encode(['status'=>'success','msg'=>'Send SMS Successfully','data'=>[]]);
            }else{
                return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
            }
        }catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            // CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('SmsController','checkSmsToBeSendNow',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

            public static function sendSMS($data)
            {
                //Temp RESPONSE
                //return 'oK';

                if(!empty($data)) {
                     $url =  env('ACLURL');
                    $mobile = $data['mobile'];
                    $message = $data['message_content'];

                    if(strlen($mobile)==10){
                        $mobile='91'.$mobile;
                        }

                    $httpQuery = http_build_query([
                            'pno' => $mobile,
                            'dcode' => env('DECODE'),
                            'subuid' => env('SUBID'),
                            'pwd' => env('SMS_PWD'),
                            'ctype' => env('CTYPE'),
                            'sender' => env('SENDER'),
                            'intflag' => env('INTFLAG'),
                            'msgtype' => env('MSGTYPE'),
                            'alert' => env('ALERT'),
                            'msgtxt' => $message
                    ]);

                    $msgData = array(
                        'sender' => 'DCBANK',
                        'route' => 4,
                        'countryCode' => 91,
                        'mobileNumber' =>  preg_replace('/\D/', '', $mobile),
                        'getGeneratedOTP' => true
                    );

                     $finalUrl = $url.$httpQuery;


                    $ch = curl_init($finalUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);

                    return $result;

                                        if(strlen($result)==31 && substr($result,0,4)=='APP-'){
                        return true;
                    }else{
                        return false;
                    }
                }
                return false;
            }


            public static function sendEmail($data)
            {
                try {
           
                    $current_time = Carbon::now()->toDateTimeString();
                    $subject = $data['email_subject'];
                    $to_email_id = $data['email'];

                    if(($subject != '') && ($to_email_id != '')){

                    $view = 'email.emailtemplate';                

                    $view_data = [ 'to_email_id' => $to_email_id,
                                    'message_content' => nl2br($data['message']),
                                    ];
                    $attachmentError = false; 
                     
                    
                    if(count($view_data)>0){

                            try{
                               
                           $view_ = Mail::send(['html' => $view], ['view_data'=>$view_data], function($message) use ($to_email_id,$subject) {
                                                            $message->to($to_email_id)
                                                                            ->subject($subject);                                                                      
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
                                    
                if($mail_error) {
                        
                        $mailMsg = 'Email Failed: ';
                        $mailMsg .= ($attachmentError ? " Attachment Error " : " ").$current_time;
                        
                } else {
                        $mailMsg = "Email Sent: ".$current_time;
                }
            
                return $mail_error;
                }
           
                }catch(\Illuminate\Database\QueryException $e) {
                    if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
                    $eMessage = $e->getMessage();                   
                    CommonFunctions::addLogicExceptionLog('NotificationController','sendEmail',$eMessage);
                    // return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
                }
        
            }


}
