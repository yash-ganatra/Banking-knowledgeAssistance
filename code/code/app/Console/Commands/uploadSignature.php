<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use App\Helpers\OaoCommonFunctions;
use DB;

class uploadSignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:uploadSignature';

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

        $maxRecord = 5;
        $signatureData = DB::table('ACCOUNT_DETAILS')->select('ID','ACCOUNT_NO')
	                                                ->whereNull('SIGNATURE_FLAG')
	                                                ->whereNotNull('ACCOUNT_NO')
                                                    ->whereNull('SOURCE')
                                                    ->whereRaw("to_char(UPDATED_AT,'YYYY-MM-DD') >= sysdate-1")
	                                                ->orderBy('ID','DESC')
	                                                ->take($maxRecord) 
	                                                ->get()->toArray();

        foreach ($signatureData as $key => $value){
            $formId = $value->id;
            $account_id = $value->account_no;
            $apiResponse = Api::uploadSignature($formId,$account_id);
            if ($apiResponse['status']=='Success') {
                $updateAccountDetails = DB::table('ACCOUNT_DETAILS')->where('ID', $formId)->where('ACCOUNT_NO',$account_id)->update(['SIGNATURE_FLAG' => 'Y']);
            }
        }

    }
}
 