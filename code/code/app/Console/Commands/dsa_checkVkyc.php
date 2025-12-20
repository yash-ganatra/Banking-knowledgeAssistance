<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;

class dsa_checkVkyc extends Command
{

    protected $signature = 'command:dsa_checkVkyc';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
     
		//sleep(15);
		
		$_MAX_RECORDS_TO_PROCESS = 5;

        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
		
		$vkycPending = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
								->select('ID', 'OAO_ID')
								->where('CUSTOMER_ID', 'Y')
                                ->where('ACCOUNT_ID', 'Y')
                                ->where('VKYC_LINK', null)
								->orderBy('ID','DESC')
								->take($_MAX_RECORDS_TO_PROCESS)->get()->toArray(); 
		
		if (count($vkycPending) == 0) { // Nothing here!
			return '';
		}	
		
		echo "\xA"; echo "Records: ".count($vkycPending);		
				
		foreach ($vkycPending as $record) {
			
			echo "\xA"; print_r('Processing: '.$record->oao_id); //exit;
			
            $oaoVkycGen = $dsaInstance->table($dsaSchema.'.ACCOUNT_LOG')
								->select('VKYC_LINK', 'VKYC_PROFILE_ID')
                                ->where('USER_ID', $record->oao_id)
								->where('CUSTOMER_ID', 'Y')
                                ->where('ACCOUNT_ID', 'Y')
                                ->where('VKYC_LINK_GENERATED', 'Y')
								->get()->toArray(); 

            if(count($oaoVkycGen)>0){
                $oaoVkycGen = current($oaoVkycGen);                
                $vkycUpdate = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
                                ->where('ID', $record->id)
                                ->where('OAO_ID', $record->oao_id)
								->where('CUSTOMER_ID', 'Y')
                                ->where('ACCOUNT_ID', 'Y')
                                ->where('VKYC_LINK', null)
								->update(['VKYC_LINK' => $oaoVkycGen->vkyc_link, 'VKYC_PROFILE_ID' => $oaoVkycGen->vkyc_profile_id]);
                 echo 'Link Updated!';               
            }
		
			
		}

	} // End of function
		


}
