<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use App\Helpers\OaoCommonFunctions;
use DB;

class kycUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kycUpdate';

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

        $maxRecord = 100;
        $checkData = DB::table('CUSTOMER_OVD_DETAILS')->leftjoin('ACCOUNT_DETAILS', 'ACCOUNT_DETAILS.ID', 'CUSTOMER_OVD_DETAILS.FORM_ID')
                                                        ->select('CUSTOMER_OVD_DETAILS.ID', 'CUSTOMER_OVD_DETAILS.FORM_ID', 'CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                                                        ->where('ACCOUNT_DETAILS.SOURCE', '<>', 'DSA')
                                                        ->where('ACCOUNT_DETAILS.SOURCE', '<>', 'CC')
                                                        ->whereNull('CUSTOMER_OVD_DETAILS.KYC_UPDATE')
                                                        ->where('CUSTOMER_OVD_DETAILS.IS_NEW_CUSTOMER',1)
                                                        ->whereNotNull('CUSTOMER_OVD_DETAILS.CUSTOMER_ID')
                                                        ->whereDate('CUSTOMER_OVD_DETAILS.CREATED_AT','>=','2022-05-26')
                                                        ->orderBy('CUSTOMER_OVD_DETAILS.FORM_ID','DESC')
                                                        ->take($maxRecord) 
                                                        ->get()->toArray();

        foreach ($checkData as $key => $value){
            $formId = $value->form_id;
            $customerId = $value->customer_id;
            $ovdId = $value->id;
            $apiResponse = Api::kycUpdate($formId,$customerId);
            if ($apiResponse == 'Success') {
                $updateAccountDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('ID', $ovdId)->where('CUSTOMER_ID',$customerId)->update(['KYC_UPDATE' => 'Y']);
            }
        }

    }
}
