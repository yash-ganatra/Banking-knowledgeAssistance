<?php

namespace App\Console\Commands;
use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Helpers\CommonFunctions;
use App\Helpers\EncryptDecrypt;
use App\Helpers\Rules;
use App\Helpers\Api;
use App\Helpers\freezeUnfreezeApi;

class dsa_createCustAcct extends Command
{

    protected $signature = 'command:dsa_createCustAcct';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
     
		//sleep(20);
	 
		$_MAX_RECORDS_TO_PROCESS = 5;	

		$dsaInstance = DB::connection('dsa');
        $cubeInstance = DB::connection('oracle');
				
        $dsaSchema = config('constants.APPLICATION_SETTINGS.DSA_SCHEMA');
        $cubeSchema = config('constants.APPLICATION_SETTINGS.CUBE_SCHEMA');

		$oaoUserDetails = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
							->leftjoin($dsaSchema.'.ACCOUNT_LOG','ACCOUNT_LOG.USER_ID','USER_DETAILS.ID')
							->select('USER_DETAILS.ID', 'USER_DETAILS.CUBE_ID', 'USER_DETAILS.CUSTOMER_ID', 'USER_DETAILS.ACCOUNT_NUMBER')
								->where('OAO_APPLICATION_STATUS', '>=', 85)
								->where('PAYMENT_STATUS', 'Y')
								//->where('USER_DETAILS.ID',842)
								->where('USER_DETAILS.CUBE_AOF', '!=', null)
								->where('USER_DETAILS.CUBE_ID', '!=', null)
								->where(function ($query) {
										$query->where('USER_DETAILS.CUSTOMER_ID', null)
											  ->orWhere('USER_DETAILS.ACCOUNT_NUMBER', null);
									})
								->where(function ($query) {
										$query->where('QID_STATUS', 'No Match')
											  ->orWhere('QID_STATUS', null) // Remove in PROD
											  ->orWhere('QID_STATUS', 'Match') // Remove in PROD
											  ->orWhere('QID_STATUS', 'Pending'); // Remove in PROD
									})
								->orderBy('USER_DETAILS.ID','DESC')
								->take($_MAX_RECORDS_TO_PROCESS)->get()->toArray(); 

		
		if (count($oaoUserDetails) == 0) { // Nothing here!
			return '';
		}	
		
		echo '<pre>'; print_r($oaoUserDetails); //exit; 
				
		foreach ($oaoUserDetails as $record) {

			echo '<pre>'; print_r($record);
			
			$dsaId = $record->id; $cubeId = $record->cube_id;			
			
			$custId = null; $acctNo = null;
			
			//echo "\xA"; print_r('Processing DSA: '.$dsaId.  ' CUBE: '.$cubeId); //exit;
			
			//--------------CUSTOMER_ID----------------------------------------------------------//
			
			if($record->customer_id == ''){
				
					print_r('Processing for CustID DSA: '.$dsaId.  ' CUBE: '.$cubeId); echo "\xA"; //exit;
					
					$custIdResponse = Self::createCustID($cubeId, $cubeInstance, $cubeSchema);			

					if(isset($custIdResponse['status']) && isset($custIdResponse['data']) && $custIdResponse['status'] != 'Error'){
						$custId = $custIdResponse['data'];

						$dsaStatus1 = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
							->where('ID', $dsaId)
							->update(['CUSTOMER_ID' => $custId]);
						$dsaStatus2 = $dsaInstance->table($dsaSchema.'.ACCOUNT_LOG')
							->where('USER_ID', $dsaId)
							->update(['CUSTOMER_ID' => 'Y']);							
						$cubeStatus = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
							->where('OAO_ID', $dsaId)
							->update(['CUSTOMER_ID' => 'Y', 'CUSTOMER_NUMBER' => $custId]);

						Rules::postDSACustomerIdApiQueue($cubeId,$custId);



					}else{						
						echo " ..nothing to process! \xA";						
					}
			}


			//--------------ACCOUNT_NUMBER----------------------------------------------------------//
			if($record->account_number == ''){

				print_r('Processing for Account No. DSA: '.$dsaId.  ' CUBE: '.$cubeId); echo "\xA";  //exit;
				
				$acctNoResponse = Self::createAcctNO($cubeId, $cubeInstance, $cubeSchema);			

				if(isset($acctNoResponse['status']) && isset($acctNoResponse['data']) && $acctNoResponse['status'] != 'Error'){
					$acctNo = $acctNoResponse['data'];

					$freezResponse = freezeUnfreezeApi::freezeApi($acctNo, $record->customer_id, 'T', $cubeId);

					$freezResponse = ($freezResponse ? 'Y' : 'N');
					
					$dsaStatus1 = $dsaInstance->table($dsaSchema.'.USER_DETAILS')
						->where('ID', $dsaId)
						->update(['ACCOUNT_NUMBER' => $acctNo]);
					$dsaStatus2 = $dsaInstance->table($dsaSchema.'.ACCOUNT_LOG')
						->where('USER_ID', $dsaId)
						->update(['ACCOUNT_ID' => 'Y']);							
					$cubeStatus = $cubeInstance->table($cubeSchema.'.OAO_STATUS')
						->where('OAO_ID', $dsaId)
						->update(['ACCOUNT_ID' => 'Y', 'FREEZE_1' => $freezResponse, 'ACCOUNT_NUMBER' => $acctNo]);
				
				}else{
					echo " ..nothing to process! \xA";
				}

			}
						
			//--------------UPDATE DSA----------------------------------------------------------//


			echo 'End: '; print_r('CustomerID: '.$custId.' AccountNo: '.$acctNo." \xA");	


		}


	} // End of function
	
	public function createCustID($cube_id, $cubeInstance, $cubeSchema)
    {        
	
		$ovd = $cubeInstance->table($cubeSchema.'.CUSTOMER_OVD_DETAILS')
						->where('FORM_ID',$cube_id)
						->where('CUSTOMER_ID', null)
						->get()->toArray();
								
		if(count($ovd) != 1){									
			return ['status'=>'Error', 'msg'=>count($ovd).' records to process!', 'data'=>[]];
		}				
		
		$ovd = (array) current($ovd);
	
		echo '<pre>Invoking CUSTID.................'; 
		return Api::createcustomerid($ovd);				
	
	}
	
	public static function createAcctNO($cube_id, $cubeInstance, $cubeSchema)
    {        
	
		$acct = $cubeInstance->table($cubeSchema.'.ACCOUNT_DETAILS')
						->leftjoin($cubeSchema.'.CUSTOMER_OVD_DETAILS','CUSTOMER_OVD_DETAILS.FORM_ID','ACCOUNT_DETAILS.ID')
							->select('ACCOUNT_DETAILS.ID', 'CUSTOMER_OVD_DETAILS.CUSTOMER_ID', 'ACCOUNT_DETAILS.ACCOUNT_NO')
								->where('ACCOUNT_DETAILS.ID',$cube_id)
								//->where('ACCOUNT_DETAILS.ACCOUNT_NO', null)
								->where('CUSTOMER_OVD_DETAILS.CUSTOMER_ID', '!=', null)
						->get()->toArray();
								
		if(count($acct) != 1){									
			return ['status'=>'Error', 'msg'=>count($acct).' records to process!', 'data'=>[]];
		}				
		
		$acct = (array) current($acct);
		
		if($acct['account_no'] == ''){
			echo '<pre>Invoking ACCTID..!!!!!!!!!!!!!...............'; 
			return Api::createaccountid($acct['id'], $acct['customer_id']);							
		}else{
			return ['status'=>'success', 'data'=>$acct['account_no']];
		}
	
	}

}
