<?php

namespace App\Console\Commands;
use App\Helpers\CommonFunctions;
use Illuminate\Console\Command;
use DB;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class apiQueueLog extends Command

{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:apiQueueLog';

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
        Log::channel('api_queue_error_log')->info('Cron Started');
        if(Cache::get('_API_QUEUE_IN_PROGRESS') != 'wip'){
            Cache::put('_API_QUEUE_IN_PROGRESS','wip',1200); // 6 min
        }else{
            Log::channel('api_queue_error_log')->info('Cron Skipped');
            exit;   
        }
        $batch = rand();    
        $recordToFetch = 30;
        $blackLists = CommonFunctions::getapplicationSettingsDetails('API_QUEUE_TO_HOLD');
        $blackLists = array_filter(explode(",",$blackLists));
        $apiQueueLogData = DB::table('API_QUEUE')->whereNull('STATUS')
                                                ->where('NEXT_RUN','<',Carbon::now())
                                                ->where('RETRY','<=',3);

        if(count($blackLists) >= 1){
            $apiQueueLogData =  $apiQueueLogData->whereNotIn('API',$blackLists);
        }

        $apiQueueLogData = $apiQueueLogData->orderBy('ID','ASC')->take($recordToFetch)->get()->toArray();
        Log::channel('api_queue_error_log')->info('Total Form Id -'.count($apiQueueLogData));

        for($i=0; $i < count($apiQueueLogData); $i++) {
            $apiQueueData =(array) $apiQueueLogData[$i];
            Log::channel('api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' Form_Id - '.$apiQueueData['form_id'].' '.$apiQueueData['api'].' In Progress');
            if($apiQueueData['retry'] > 3){
                Log::channel('api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' Form_Id - '.$apiQueueData['form_id'].' Failed Due to Retry Count Exceed - '.$apiQueueData['retry']);
                continue;
            }
            try{
                $parameter = json_decode($apiQueueData['parameter']);
                $className = app("App\\Helpers\\".$apiQueueData['class']);
                $callFunction = call_user_func(array($className,$apiQueueData['api']), array($parameter));
                if(isset($callFunction['status']) && $callFunction['status'] == 'success'){
                    if($apiQueueData['retry'] != '0'){
                        $apiQueueData['retry'] = 1 + $apiQueueData['retry'];
                    }
                    Log::channel('api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' Form_Id - '.$apiQueueData['form_id'].' '.$apiQueueData['api'].' Api Success');

                    DB::table('API_QUEUE')->whereId($apiQueueData['id'])->where('FORM_ID',$apiQueueData['form_id'])->update(['STATUS' => 'Y','UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $apiQueueData['retry'],'NEXT_RUN' => null,'UPDATED_BY' => 1]);

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
                    Log::channel('api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' Form_Id - '.$apiQueueData['form_id'].' Api Failed');
                    DB::table('API_QUEUE')->whereId($apiQueueData['id'])->where('FORM_ID',$apiQueueData['form_id'])->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => json_encode($callFunction),'RETRY' => $retryCount,'NEXT_RUN' => $next_run,'UPDATED_BY' => 1]);
                DB::commit();
                }
            }catch(\Throwable $e){
                $eMessage = $e->getMessage();
                Log::channel('api_queue_error_log')->info($batch.'_'.$i.' - > Api Queue Id -'.$apiQueueData['id'].' Form_Id - '.$apiQueueData['form_id'].' '.$apiQueueData['api'].' Api Exception '.$eMessage);
                    DB::table('API_QUEUE')->whereId($apiQueueData['id'])->where('FORM_ID',$apiQueueData['form_id'])->update(['UPDATED_AT' => Carbon::now(),'API_RESPONSE' => 'ExceptionAPI', 'RETRY' =>4 ,'UPDATED_BY' => 1]);
                DB::commit();
            }
            //continue;
        }
        Cache::put('_API_QUEUE_IN_PROGRESS','done');
    }
}

?>
