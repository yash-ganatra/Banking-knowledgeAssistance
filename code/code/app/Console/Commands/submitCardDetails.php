<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use App\Helpers\OaoCommonFunctions;
use DB;

class submitCardDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:submitCardDetails';

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

        // FOR CUBE
        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('NEXT_ROLE', '>', 4)
                                                ->whereIn('ACCOUNT_TYPE',  ['1','4','5'])
                                                ->whereNull('SOURCE')
                                                ->where(function ($query) {
                                                $query->where('CARD_FLAG', '!=', 'Y')
                                                      ->orWhereNull('CARD_FLAG');
                                                })
                                                ->get()->toArray();

        if (count($accountDetails) > 0) {
                Self::processCard($accountDetails);           
        }

        // FOR DSA
        $accountDetails = DB::table('ACCOUNT_DETAILS')->where('SOURCE', 'DSA')
                                                //->whereIn('ACCOUNT_TYPE',  ['1','4','5'])
                                                ->whereNotNull('ACCOUNT_NUMBER')
                                                ->where(function ($query) {
                                                $query->where('CARD_FLAG', '!=', 'Y')
                                                      ->orWhereNull('CARD_FLAG');
                                                })
                                                ->get()->toArray();

        if (count($accountDetails) > 0) {
                Self::processCard($accountDetails);           
        }
    }

    public function processCard($accountDetails){
        foreach ($accountDetails as $key => $value){
            $formId = $value->id;

            $checkWaiting = DB::table('ACCOUNT_DETAILS')->whereId($formId)->get()->toArray();
            $checkWaiting = current($checkWaiting);

            if (isset($checkWaiting->card_flag) && $checkWaiting->card_flag == 'W') {
                continue;
            }

            $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'W']);

            $apiResponse = Api::submitCardDetails($formId);

            if($apiResponse != 'Success'){
                echo "failed ".$formId;
                $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'N']);
            }else{
                echo "success ".$formId;
                $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->whereId($formId)->update(['CARD_FLAG' => 'Y']);
            }
                
        }
    }

}
