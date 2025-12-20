<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Helpers\CommonFunctions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DB;
use Cookie;
use Crypt;

class CurrentDayReportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        if(Cookie::get('token') != ''){
            //decrypt token to get claims which include params
            $this->token = Crypt::decrypt(Cookie::get('token'),false);
            //get claims from token
            $encoded = explode('.',$this->token)[1];
            //get params from claims with json decode and base64 decoding
            $userDetails = json_decode(base64_decode($encoded),true);
            //get userId by userDetails
            $this->userId = $userDetails['user_id'];
            //get roleId by userDetails
            $this->roleId = $userDetails['role_id'];

            if(in_array($this->roleId,[1,8,13])){
               $isAutherized = true;
            }else{
                $isAutherized = false;
            }

            if(!$isAutherized)
            {
                echo "<div class='container RefreshRestrictMsg' style='
                    width: 63%;
                    margin: 0 auto;
                    height: 4em;
                    padding: 2em;
                    text-align: center;
                    border-radius: 10px red;
                    border-radius: 6px;
                    margin-top: 12em;
                    background-color: #fff0d3;
                    font-family:Arial;
                    line-height: 35px;'>

                    <p style='margin-top: 0%;
                    font-size: 1.375rem;
                    font-weight: 500;'>Unauthorized attempt detected.<br>Event logged for Audit and Admin team.</p>
                 
                  </div>";
                $saveuserlog = CommonFunctions::createUserLogDirect('NPC/DashboardController','dashboard','Unauthorized attempt detected by '.$this->userId,'','','1');

                 header('Refresh: 5; URL= ../login');
                 die();
            }

        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public  function currentDayReport()
    {
		$completionData = Self::getTodaysCompletionStatus();
		$pendingData = Self::getTodaysPendingStatus();
        return view('reports.currentDayReport')
					->with('completionData', $completionData)
					->with('pendingData', $pendingData)
					;
    }
    

/*------------------ Current Day Dashboard -------------------------*/

public function getTodaysCompletionStatus(){
	
		// Function to compute today's performance (completed forms) and tat break up

		$allFields = [ 'BRANCH_SUBMISSION', 'L1', 'L2', 'QC', 'AUDITING'];
		$allData = array();
		foreach($allFields as $field){
				$gorData = Self::getTodaysCompletionStatus_forRole($field);
				$currResponse = [0,0,0];
				foreach($gorData as $key => $value){
					$currBlock = $value;					
					switch($currBlock->status){
						case 'GREEN': 
							$currResponse[0] = (int) $currBlock->count; break;
						case 'ORANGE': 
							$currResponse[1] = (int) $currBlock->count; break;
						case 'RED': 
							$currResponse[2] = (int) $currBlock->count; break;							
					}						
				}
				array_push($allData, $currResponse );
		}						
		//echo '<pre>'; print_r($allData); 		exit;								
		return $allData;
	}
	
public function getTodaysCompletionStatus_forRole($field){

		// called by gettodaygorstatus
	
		$response =  DB::table('ACCOUNT_STATUS_UPDATE_METRICS')
						->select(
							DB::raw("CASE WHEN ".$field." < 30  THEN 'GREEN' 
											WHEN ".$field." < 120 THEN 'ORANGE'
											ELSE 'RED' END AS STATUS, count(*) AS COUNT"))
						->whereRaw("trunc(SUBMISSION_DATE) = trunc(sysdate)")
						->whereNotNull($field)
						->groupBy(DB::raw("CASE WHEN ".$field." < 30 THEN 'GREEN' 
												WHEN ".$field." < 120 THEN 'ORANGE'
												ELSE 'RED' END"))
						->get()->toArray();		
		//echo '<pre>'.$field; print_r($response); 								
		return $response;
		
}


public function getTodaysPendingStatus(){
	
		// Function to compute today's performance (completed forms) and tat break up

		$allFields = [ 'BRANCH_NEW', 'BRANCH_DISCREPENT', 'L1', 'L2', 'L3', 'QC', 'AUDIT'];
		$allRoles  = [        NULL,                   2,    3,   4,     8,	   5,	    6];
		$gorZero = [0,0,0]; // G O Red!
		$currResponse = array($gorZero, $gorZero, $gorZero, $gorZero, $gorZero, $gorZero, $gorZero);
		
		$gorData =  DB::table('ACCOUNT_DETAILS')
				->select('NEXT_ROLE',
						DB::raw("CASE WHEN UPDATED_AT > sysdate - (interval '30' minute)  THEN 'GREEN' 
									WHEN UPDATED_AT > sysdate - (interval '120' minute)  THEN 'ORANGE' 
									ELSE 'RED' END AS STATUS, 
							  count(*) AS COUNT"))
				->where(function ($query)   {
						$query->whereIn("NEXT_ROLE",array(2, 3, 4, 5, 6, 8))
							->orWhere("NEXT_ROLE", null);
						  })	
				->whereNotIn("APPLICATION_STATUS", array(5,12, 45))
			//	->where("L2_CLEARED_STATUS",0)
				->groupBy('NEXT_ROLE', 
							DB::raw("CASE WHEN UPDATED_AT > sysdate - (interval '30' minute)  THEN 'GREEN' 
									WHEN UPDATED_AT > sysdate - (interval '120' minute)  THEN 'ORANGE' 
									ELSE 'RED' END")
						  )
				->orderBy('NEXT_ROLE')		  
				->get()->toArray();				
						
		foreach($gorData as $key => $value){
				$currBlock = $value;					
				switch($currBlock->next_role){
					case '2':  // BRANCH_DISCREPENT
						if($currBlock->status == 'GREEN'){
							$currResponse[1][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[1][1] = $currBlock->count;
						}else{
							$currResponse[1][2] = $currBlock->count;
						}
						break;
					case '3': // L1
						if($currBlock->status == 'GREEN'){
							$currResponse[2][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[2][1] = $currBlock->count;
						}else{
							$currResponse[2][2] = $currBlock->count;
						}
						break;
					case '4':  // L2
						if($currBlock->status == 'GREEN'){
							$currResponse[3][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[3][1] = $currBlock->count;
						}else{
							$currResponse[3][2] = $currBlock->count;
						}
						break;
					case '8': // L3
						if($currBlock->status == 'GREEN'){
							$currResponse[4][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[4][1] = $currBlock->count;
						}else{
							$currResponse[4][2] = $currBlock->count;
						}
						break;
					case '5':  // QC
						if($currBlock->status == 'GREEN'){
							$currResponse[5][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[5][1] = $currBlock->count;
						}else{
							$currResponse[5][2] = $currBlock->count;
						}
						break;
					case '6': // AU
						if($currBlock->status == 'GREEN'){
							$currResponse[6][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[6][1] = $currBlock->count;
						}else{
							$currResponse[6][2] = $currBlock->count;
						}
						break;
					default: // Branch New / NULL	
						if($currBlock->status == 'GREEN'){
							$currResponse[0][0] = $currBlock->count;
						}elseif($currBlock->status == 'ORANGE'){
							$currResponse[0][1] = $currBlock->count;
						}else{
							$currResponse[0][2] = $currBlock->count;
						}
						break;
				}						
			}							
		//echo '<pre>'; print_r($gorData); print_r($currResponse);								
		return $currResponse;
	}
	

/*--------------------- END CURRENT DAY DASHBOARD -------------*/


}

?>