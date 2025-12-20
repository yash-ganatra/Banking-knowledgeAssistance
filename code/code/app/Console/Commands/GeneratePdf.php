<?php

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use Carbon\Carbon;

use File;
use DB;
use PDF;
use App\Http\Controllers\Admin\ExceptionController;

class GeneratePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GeneratePdf {--aof=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $aof = $this->option('aof');
        echo "<pre>Input Received: ".$aof.'.';

        $accounts = DB::table('ACCOUNT_DETAILS')->where('AOF_NUMBER',$aof)->get()->toArray();
        $countOfPdfs = 0;

        foreach ($accounts as $account) {

            $aofNumber = $account->aof_number;
            // DB::table('ACCOUNT_DETAILS')->where('ID',$account->id)->update(['PDF_GENERATED'=> 's']);
            echo "<pre>Processing: ".$aofNumber." formID: ".$account->id;

            // if(file_exists('conf_data/email_aof/DCB-CUBE-'.$aofNumber.'.pdf')){
            //  echo "<pre>";print_r('Skipping already generated PDF!');
            //     continue;
            // }
            
            // $aofNumber = 21070000902;
            $custIds = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID',$account->id)
                                                        ->where('CUSTOMER_ID', '!=', null)
                                                        ->get()->toArray();

            

            if (count($custIds) == 0) {
             echo "<pre>";print_r('CustID not generated..');
                continue;
            }

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
            try{
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
                //echo "<pre>";print_r($aofNumber);

                $enc_fields=[];
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
                    'formId'=>$formId,
                    'accountType'=>$accountType,
                    'huf_cop_row'=>$huf_cop_row,
                    'enc_fields'=>$enc_fields,
                    'userDetails'=>$formDetailsArray['userDetails'],
                    'files'=>$formDetailsArray['files'],
                    'declarationsList'=>$formDetailsArray['declarationsList'],
                    'username'=>$formDetails['empldapuserid'],
                    'nomineeDetails'=>$formDetailsArray['nomineeDetails'],
                    'cifDeclarationDetails'=>$cifDeclarationDetails]);
                $pdf = PDF::loadView('bank.submissionform');
                // return view('bank.submissionform');
                $pdf->setEncryption($pdfPass);
                $pfdpath = base_path('/conf_data/email_aof/DCB-CUBE-'.$aofNumber.'.pdf');
                if(file_exists($pfdpath)){
                    unlink($pfdpath);
                }
                $pdf->save($pfdpath);

                print_r('<pre> DCB-CUBE-'.$aofNumber.'.pdf');

                $customNotif = Self::processCustomerNotificationforPROD($formId,'CUSTID_EMAIL');
                // DB::table('ACCOUNT_DETAILS')->where('ID',$account->id)->update(['PDF_GENERATED'=> 'p']);
				
				echo '<pre>Notification inserted, Please check Email_SMS_Log!';
				
             }catch(\Illuminate\Database\QueryException $e) {
                echo "<pre>";print_r('Could not process: '.$aofNumber);
             }
            $countOfPdfs++;
        }
            echo "<pre>";print_r($countOfPdfs);exit;
        // $response = $commonFunctions->getFormDetails(7522);  
        // print_r($response);
    }

    public static function processCustomerNotificationforPROD($formId,$activitycode){

        $sendNotificationTo = 'ALL';

        $messages = CommonFunctions::getMessages($activitycode);

        $aofNumber = DB::table('ACCOUNT_DETAILS')->whereId($formId)->pluck('aof_number')->toArray();
        $aofNumber = current($aofNumber);

        $ovddetails = DB::table('CUSTOMER_OVD_DETAILS')
                                                      ->select('ID','MOBILE_NUMBER','EMAIL','FIRST_NAME','LAST_NAME','MIDDLE_NAME','APPLICANT_SEQUENCE')
                                                      ->where('form_id',$formId)
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

                  $filename = 'DCB-CUBE-'.$aofNumber.'.pdf';
                  $filePath = base_path('/conf_data/email_aof/'.$filename);

            }else{
                $filePath = '';
            }
            $currentDBtime = CommonFunctions::getCurrentDBtime();


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
                                                    'email_response' => 'Created offline'
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
}