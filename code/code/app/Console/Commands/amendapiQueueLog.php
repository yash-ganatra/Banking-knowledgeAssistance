<?php

namespace App\Console\Commands;
use App\Helpers\CommonFunctions;
use Illuminate\Console\Command;
use DB;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class amendapiQueueLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:amendapiQueueLog';

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
        // Log::channel('amend_api_queue_error_log')->info('Cron Started');
        // if(Cache::get('_AMEND_API_QUEUE_IN_PROGRESS') != 'wip'){
        //     Cache::put('_AMEND_API_QUEUE_IN_PROGRESS','wip',1200); // 6 min
        // }else{
        //     Log::channel('amend_api_queue_error_log')->info('Cron Skipped');
        //     exit;   
        // }
        $batch = rand();    
        $recordToFetch = 20;
        $blackLists = CommonFunctions::getapplicationSettingsDetails('API_QUEUE_TO_HOLD');
        $blackLists = array_filter(explode(",",$blackLists));

            if(env('APP_SETUP') == 'DEV'){
                $schema = 'NPC';
            }else{
                $schema = 'CUBE';
            }    
    
            $getoneMonth = Carbon::now()->subMonth(1)->format('Y-m-d');
            $currentDate = Carbon::now();

            $apiQueueLogData = DB::select("SELECT * FROM $schema.AMEND_API_QUEUE WHERE (CRF_NUMBER,SEQUENCE) IN (SELECT CRF_NUMBER,MIN(SEQUENCE) FROM $schema.AMEND_API_QUEUE  WHERE STATUS IS NULL AND CREATED_AT >= to_date('".$getoneMonth."','YYYY-MM-DD') AND NEXT_RUN <= to_date('".$currentDate."','YYYY-MM-DD hh24:mi:ss') AND RETRY < 3 GROUP BY CRF_NUMBER) ORDER BY CRF_NUMBER ASC");
            // $apiQueueLogData = DB::select("SELECT * FROM $schema.AMEND_API_QUEUE WHERE (CRF_NUMBER,SEQUENCE) IN (SELECT CRF_NUMBER,MIN(SEQUENCE) FROM $schema.AMEND_API_QUEUE  WHERE STATUS IS NULL GROUP BY CRF_NUMBER) ORDER BY CRF_NUMBER ASC");
            Log::channel('amend_api_queue_error_log')->info('Total CRF Number -'.count($apiQueueLogData));

        $recordtoProcess = count($apiQueueLogData) > $recordToFetch ? $recordToFetch : count($apiQueueLogData);

        for($i=0; $i < $recordtoProcess; $i++) {
            $apiQueueData =(array) $apiQueueLogData[$i];
            Log::channel('amend_api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' CRF_NUMBER - '.$apiQueueData['crf_number'].' '.$apiQueueData['api'].' In Progress');
            if($apiQueueData['retry'] > 3){
                Log::channel('amend_api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' CRF_NUMBER - '.$apiQueueData['crf_number'].' Failed Due to Retry Count Exceed - '.$i);
                continue;
            }
            try{
                $parameter = json_decode($apiQueueData['parameter']);
                $className = app("App\\Helpers\\".$apiQueueData['class']);
                $callFunction = call_user_func_array(array($className,$apiQueueData['api']), $parameter);
                $callFunction = json_decode($callFunction,true);
                if(isset($callFunction['status']) && strtolower($callFunction['status']) == 'success'){
                    if($apiQueueData['retry'] != '0'){
                        $apiQueueData['retry'] = 1 + $apiQueueData['retry'];
                    }
                    Log::channel('amend_api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' CRF_NUMBER - '.$apiQueueData['crf_number'].' '.$apiQueueData['api'].' Api Success');

                    DB::table('AMEND_API_QUEUE')->whereId($apiQueueData['id'])->where('CRF_NUMBER',$apiQueueData['crf_number'])->update(['STATUS' => 'Y','UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $apiQueueData['retry'],'NEXT_RUN' => null,'UPDATED_BY' => 1]);

                    DB::commit();
                }else{
                    $retryCount = 1 + $apiQueueData['retry'];
                    switch ($retryCount){

                        case '1':
                            $interValTime = 30;
                        break;

                        case '2':
                            $interValTime = 60;
                        break;

                        case '3':
                            $interValTime = 120;
                        break;

                        case '4':
                            $interValTime = 120;
                        break;
                    }
                    $next_run = Carbon::now()->addMinutes($interValTime);
                    Log::channel('amend_api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' CRF_NUMBER - '.$apiQueueData['crf_number'].' Api Failed');
                    DB::table('AMEND_API_QUEUE')->whereId($apiQueueData['id'])->where('CRF_NUMBER',$apiQueueData['crf_number'])->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $retryCount,'NEXT_RUN' => $next_run,'UPDATED_BY' => 1]);
                DB::commit();
                }
            }catch(\Throwable $e){
                $eMessage = $e->getMessage();
                Log::channel('amend_api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' CRF_NUMBER - '.$apiQueueData['crf_number'].' '.$apiQueueData['api'].' Api Exception '.$eMessage);
                    DB::table('AMEND_API_QUEUE')->whereId($apiQueueData['id'])->where('CRF_NUMBER',$apiQueueData['crf_number'])->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => 'ExceptionAPI', 'RETRY' =>1 ,'UPDATED_BY' => 1]);
                DB::commit();
            }
            //continue;
        }
        Cache::put('_AMEND_API_QUEUE_IN_PROGRESS','done');
    }
}

?>
