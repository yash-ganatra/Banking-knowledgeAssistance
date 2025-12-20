<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;

class dsa_checkSettlement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dsa_checkSettlement';

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
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

        $dsaInstance = DB::connection('dsa');

        for ($i=1; $i <= 3; $i++) { 
            $date = Carbon::now()->subDay($i)->format('Y-m-d');
            Self::SettleMentApi($date);
        }

        // $getSettlementLogs = $dsaInstance->table($dsaSchema.'.PG_SETTLEMENT')->whereNotNull('STATUS')->get()->toArray();
        // for ($i=0; $i < count($getSettlementLogs); $i++) { 
        //     $settlementData = $getSettlementLogs[$i];
        //     $pgLogsData = $dsaInstance->table($dsaSchema.'.PG_LOGS')->where('TXN_ID',$settlementData->txn_id)->get()->toArray();
        //     if(count($pgLogsData) >= 1){
        //         $dsaInstance->table($dsaSchema.'.PG_LOGS')->where('TXN_ID',$settlementData->txn_id)->update(['IS_SETTLEMENT' => 'Y']);
        //           DB::commit();
        //     }
        // }
    }

    public function SettleMentApi($date){
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $dsaInstance = DB::connection('dsa');
        $merchant_key = 'IV1CLz';
        $command = 'get_settlement_details';
        $hash = hash('sha512', $merchant_key.'|'.$command.'|'.$date.'|'.'XPFBuVE0FFUw38eZ78K1BwwGqZ5jn30g');

        $data = http_build_query([
            'key' => 'IV1CLz',
            'command' => $command,
            'var1' => $date,
            'hash' => $hash,
            'form'=>'2-H'
        ]); 

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://test.payu.in/merchant/postservice",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 90000,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
          ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $response = Self::settlementSuccessArray();
        if($response->status == 1){
            if(count($response->Txn_details) >= 1){
                for($i=0; $i < $response->Txn_details; $i++){ 
                    $txnDetails = $response->Txn_details[$i];
                    $countPGSettlement = $dsaInstance->table($dsaSchema.'.PG_SETTLEMENT')->where('TXN_ID',$txnDetails['txnid'])->get()->toArray();
                    if(count($countPGSettlement) >= 1){
                      if($i === array_key_last($response->Txn_details)){
                        break;
                      }
                      $i++;
                    }
                    $pgLog = $dsaInstance->table($dsaSchema.'.PG_LOGS')->select('USER_ID')->where('TXN_ID',$txnDetails['txnid'])->get()->toArray();
                    $pgLog = (array) current($pgLog);
                    $insertData = ['TXN_ID' => $txnDetails['txnid'],
                                   'AMOUNT' => $txnDetails['amount'],
                                    'SERVICE_RESPONSE' => json_encode($response),
                                    'STATUS' => 'Y',
                                    'DATE' => $txnDetails['txndate'],
                                    'PAYU_ID' => $txnDetails['payuid'],
                                    'CREATED_BY' => '1',
                                    'CREATED_AT' => Carbon::now(),
                                    'USER_ID' => $pgLog['user_id']
                                    ];
                    $checkRecordExist = $dsaInstance->table($dsaSchema.'.PG_SETTLEMENT')->where('TXN_ID',$txnDetails['txnid'])->count();
                    if($checkRecordExist == 0){
                      $dsaInstance->table($dsaSchema.'.PG_SETTLEMENT')->insert($insertData);
                      $dsaInstance->table($dsaSchema.'.PG_LOGS')->where('TXN_ID',$txnDetails['txnid'])->update(['IS_SETTLEMENT' => 'Y']);
                    }

                    DB::commit();
                    if($i === array_key_last($response->Txn_details)){
                        break;
                    }
                    $i++;
                }
            }
        }
    }

    public function settlementSuccessArray(){
      $successArray = Array(
        "status" => '1',
        "msg" => '1 transactions settled on 2021-08-11',
        'Txn_details' => Array
        (
          Array
                (
                    'payuid' => '13799177287',
                    'txnid' => '697d931ef46272293a2380344',
                    'txndate' => "2021-08-10 23:46:25",
                    'mode' => 'DC',
                    'amount' => '11979.88',
                    'requestid' => '9586840660',
                    'requestdate' => '2021-08-10 23:49:16',
                    'requestaction' => 'capture',
                    'requestamount' => '11979.88',
                    'mer_utr' => 'N223211598444659',
                    'mer_service_fee' => '239.6000',
                    'mer_service_tax' => '43.1300',
                    'mer_net_amount' => '11697.1500',
                    'bank_name' => 'MAST',
                    'issuing_bank' => 'SBI',
                    'merchant_subvention_amount' => '0.00',
                    'cgst' => '0.00000',
                    'igst' => '43.13000',
                    'sgst' => '0.00000',
                    'PG_TYPE' => 'HDFC_Internal_Plus',
                    'Card Type' => '',
                    'token' => '6112241bc5877_f4bdd97e',
                    'SettlementType' => 'regular',
                    'PG' => 'HDFC_Internal_Plus',
                    'Scheme' => 'CC',
                    'FeeType' => 'tdrFee',
                    'InstantSettlementTDR' => '0.0',
                    'InstantSettlementTDRTax' => '0.0',
                    'InstantSettlementTdrType' => '',
                    'InstantRefundTDR' => '0.0',
                    'InstantRefundTDRTax' => '0.0',
                    'InstantRefundTdrType' => '',
                )

        ));
      $successArray = (object) $successArray;
      return $successArray;
    } 
}
