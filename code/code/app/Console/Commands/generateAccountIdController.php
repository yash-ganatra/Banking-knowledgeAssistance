<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NPC\ReviewController;
use App\Helpers\CommonFunctions;
use DB;


class generateAccountIdController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generateAccountIds';

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
          $no_of_account_holder = CommonFunctions::getapplicationSettingsDetails('NO_OF_ACCOUNT_CRON');

          $accountDetails = DB::table('ACCOUNT_DETAILS')->leftjoin('FINCON','FINCON.FORM_ID','ACCOUNT_DETAILS.ID')
                                                    ->where('ACCOUNT_DETAILS.NEXT_ROLE',1)
                                                    ->where('ACCOUNT_DETAILS.EXTERNAL_ID', null)
                                                    ->where('ACCOUNT_DETAILS.L2_CLEARED_STATUS', 1)
                                                    ->whereIn('ACCOUNT_DETAILS.ACCOUNT_TYPE',[1,3,4])
                                                    ->whereNotIn('ACCOUNT_DETAILS.APPLICATION_STATUS',[4,5,16,19,26,45])
                                                    ->where('ACCOUNT_DETAILS.ACCOUNT_NO', null)
                                                    ->where('FINCON.FUNDING_STATUS', 'Y')
                                                    ->where('ACCOUNT_DETAILS.APPLICATION_STATUS','<>',14)
                                                    ->orderBy('ACCOUNT_DETAILS.ID','DESC')
                                                    // ->take(100)
                                                    ->take($no_of_account_holder)
                                                    ->get()->toArray();
                                                    
        $ReviewController = new ReviewController(); 
        for($f=0; $f < count($accountDetails); $f++){

            $customerDetails = DB::table('CUSTOMER_OVD_DETAILS')->where('FORM_ID', $accountDetails[$f]->form_id)
                                ->where('CUSTOMER_ID',NULL) //custid blank or not created
                                ->get()->toArray();
                                
            if(count($customerDetails) == 0) {                
                        
                $isAccountCreated = CommonFunctions::isAccountCreated($accountDetails[$f]->form_id);
                $isFundingDone = CommonFunctions::preFlightFTR($accountDetails[$f]->form_id);

                if ($isFundingDone && !$isAccountCreated) {
                    $ReviewController->createaccountnumberInternal($accountDetails[$f]->form_id, 'cron');
                    $ReviewController->markFormForQCInternal($accountDetails[$f]->form_id);
                }
            }
        }
    }
}
