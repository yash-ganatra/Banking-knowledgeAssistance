<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Http\Controllers\NPC\ReviewController;
use App\Helpers\CommonFunctions;
use App\Helpers\OaoCommonFunctions;
use DB;

class checkOaoFTR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkOaoFTR';

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

    	$checkFTRPending = DB::table('FINCON')->where('FUNDING_STATUS', 'Y')
                                    		->whereIn('FUNDING_TYPE',['11','12'])
                                            ->where('FTR_STATUS','N')
                                            ->get()->toArray();
        // echo "<pre>";print_r($checkFTRPending);exit;

        $ReviewController = new ReviewController(); 
        for($f=0; $f < count($checkFTRPending); $f++){
            $ReviewController->checkFundTransfer($checkFTRPending[$f]->form_id, 'cron');
            // echo "<pre>";print($checkFTRPending[$f]->form_id);exit;
            $ReviewController->markFormForQCInternal($checkFTRPending[$f]->form_id);
        }

        $cubeQueryInstance = DB::connection('oracle');
        $finacleQueryInstance = DB::connection('oracle2');

        $oaoStatusDetails = $cubeQueryInstance->table('OAO_STATUS')->where('FUND_RECEIVED','Y')
                                                                ->where('FTR', null)
                                                                ->where('CUSTOMER_ID','Y')
                                                                ->where('ACCOUNT_ID','Y')
                                                                ->where('FREEZE_2','Y')->get()->toArray();

       
        for($i=0; $i < count($oaoStatusDetails); $i++){
            // echo "<pre>";print_r($oaoStatusDetails);exit;

            $checkFTRDone = $cubeQueryInstance->table('FINCON')->where('FORM_ID', $oaoStatusDetails[$i]->form_id)
                                                    ->where('FUNDING_STATUS', 'Y')
                                                    ->whereIn('FUNDING_TYPE',['11','12'])
                                                    ->where('FTR_STATUS','Y')
                                                    ->get()->toArray();
            
            if(count($checkFTRDone) > 0){ 
                $checkFTRDone = current($checkFTRDone);
                $updateOaoStatus = DB::table('OAO_STATUS')->where('FORM_ID', $checkFTRDone->form_id)
                                                            ->update(['FTR' => 'Y']); 
            }
        }

        $cubeQueryInstance->disconnect();
        $finacleQueryInstance->disconnect();

    }
}
