<?php

namespace App\Console\Commands;
use DB;
use Illuminate\Console\Command;

class checkGeneratedVkycLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkGeneratedVkycLink';

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
        $finacleQueryInstance = DB::connection('oracle2');
        $cubeQueryInstance = DB::connection('oracle');


        $oaoStatusDetails = $cubeQueryInstance->table('OAO_STATUS') 
                                        ->where('IS_KIT_ALLOCATED' , 'Y')
                                        ->where('FTR' , 'Y')
                                        ->where(function ($query) {
                                        $query->where('VKYC_STATUS', '!=', 'completed')
                                              ->orWhereNull('VKYC_STATUS');
                                        })
                                        ->get()->toArray();
        // echo "<pre>";print_r($oaoStatusDetails);exit;

        if (count($oaoStatusDetails)> 0) {
            foreach ($oaoStatusDetails as $oaoStatus) {
                $checkWaiting = $cubeQueryInstance->table('OAO_STATUS')->where('ID', $oaoStatus->id)->get()->toArray();
                $checkWaiting = current($checkWaiting);

                $accountLog = $finacleQueryInstance->table('OAO.ACCOUNT_LOG')->where('user_id',$oaoStatus->oao_id)->get()->toArray();
                $accountLog = current($accountLog);

                if ($checkWaiting->vkyc_status == 'W' || $checkWaiting->vkyc_status == 'link generated') {
                    continue;
                }

                $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['VKYC_STATUS'=> 'W']); 

                if ($accountLog->vkyc_link_generated == 'Y') {
                    $updateVkycStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['VKYC_STATUS'=> 'link generated']); 
                    
                    if (!$updateVkycStatus) {
                        $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['VKYC_STATUS'=> 'F']); 
                    }
                }else{
                    $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['VKYC_STATUS'=> 'F']); 
                }

            }
        }
        
        $finacleQueryInstance->disconnect();
        $cubeQueryInstance->disconnect();
        // $insertOaoDetails = $cubeQueryInstance->table('OAO_STATUS')->insert('');
    }
}
