<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\NPC\ReviewController;
use App\Http\Controllers\NPC\PrivilegeAccessController;
use App\Helpers\CommonFunctions;
use App\Helpers\OaoCommonFunctions;
use App\Helpers\Api;
use App\Helpers\AmendApi;

class oaoAllocateKit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:oaoAllocateKit';

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

        $oaoStatusDetails = $cubeQueryInstance->table('OAO_STATUS')->where('IS_COPIED_1','Y')
                                                            ->where('IS_COPIED_2','N')
                                                            ->where('QUERY_ID','Y')
                                                            ->where('DEDUPE_STATUS','Y')
                                                            ->where(function ($query) {
                                                                $query->where('IS_KIT_ALLOCATED', '!=', 'Y')
                                                                  ->orWhereNull('IS_KIT_ALLOCATED');
                                                            })
                                                            ->get()->toArray();
                                                            
        if (count($oaoStatusDetails)> 0) {
            foreach ($oaoStatusDetails as $oaoStatus) {
                $userDetails = $finacleQueryInstance->table('OAO.ACCOUNT_LOG')->where('user_id', $oaoStatus->oao_id)
                                                                            ->where('payment_status', 'Y')
                                                                            ->where('customer_id',  null)
                                                                            ->where('account_id',  null)->get()->toArray();

                if (count($userDetails) > 0){
                    $userDetails = current($userDetails);

                    $checkWaiting = $cubeQueryInstance->table('OAO_STATUS')->where('ID', $oaoStatus->id)->get()->toArray();
                    $checkWaiting = current($checkWaiting);
                    if ($checkWaiting->payment == 'Y' || $checkWaiting->payment == 'W') {
                        continue;
                    }

                    $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['PAYMENT'=> 'W']);
                    
                    $cubeAccountDetails = DB::connection('oracle')->table("ACCOUNT_DETAILS")->where('id', $oaoStatus->form_id)->get()->toArray();
                    $cubeAccountDetails = current($cubeAccountDetails);

                    // echo "<pre>";print_r($cubeAccountDetails);exit;
                    if($cubeAccountDetails->delight_kit_id == null) {
                        Self::setKitDetails($finacleQueryInstance, $cubeQueryInstance, $oaoStatus->oao_id, $oaoStatus->form_id);
                        $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $oaoStatus->oao_id)->update(['PAYMENT'=> 'Y']);
                    }

                }
            }
        }
        
        $finacleQueryInstance->disconnect();
        $cubeQueryInstance->disconnect();
        // $insertOaoDetails = $cubeQueryInstance->table('OAO_STATUS')->insert('');
    }


   public static function setKitDetails($finacleQueryInstance,$cubeQueryInstance, $userId, $formId)
    {
        $cubeKitsDetails = $cubeQueryInstance->table('DELIGHT_KIT')->where('STATUS','7')->where('SALES_USER_ID', null)->get()->toArray();
        echo "<pre>";print_r(count($cubeKitsDetails));
        if (count($cubeKitsDetails) > 0) {
            $cubeKitsDetails = current($cubeKitsDetails);

            OaoCommonFunctions::updateOaoFields('OAO.USER_DETAILS',$userId,'CUSTOMER_ID',$cubeKitsDetails->customer_id);
            OaoCommonFunctions::updateOaoFields('OAO.ACCOUNT_LOG',$userId,'CUSTOMER_ID','Y');

            OaoCommonFunctions::updateOaoFields('OAO.USER_DETAILS',$userId,'ACCOUNT_NUMBER',$cubeKitsDetails->account_number);
            OaoCommonFunctions::updateOaoFields('OAO.ACCOUNT_LOG',$userId,'ACCOUNT_ID','Y');

            $updateOvdDetails = $cubeQueryInstance->table("ACCOUNT_DETAILS")->where('id', $formId)->update(['DELIGHT_KIT_ID'=>$cubeKitsDetails->id]);
            if ($updateOvdDetails) {
                $updateKitDetails = $cubeQueryInstance->table('DELIGHT_KIT')->where('id',$cubeKitsDetails->id)->update(['STATUS'=>'8']);
                $updateOaoStatus = $cubeQueryInstance->table('OAO_STATUS')->where('OAO_ID', $userId)->update(['IS_KIT_ALLOCATED'=> 'Y', 'FREEZE_1' => 'N']); 
                echo "kit allocated";
            }

        }
    }


}
